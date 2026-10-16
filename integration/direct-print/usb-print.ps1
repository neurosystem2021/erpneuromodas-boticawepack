param(
    [Parameter(Mandatory = $true)][string]$PrinterName,
    [Parameter(Mandatory = $true)][string]$FilePath
)

$source = @'
using System;
using System.IO;
using System.Runtime.InteropServices;

public static class RawPrinter
{
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public class DOC_INFO_1
    {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }

    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    static extern bool OpenPrinter(string pPrinterName, out IntPtr phPrinter, IntPtr pDefault);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool ClosePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    static extern int StartDocPrinter(IntPtr hPrinter, int level, DOC_INFO_1 di);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool EndDocPrinter(IntPtr hPrinter);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool StartPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool EndPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv", SetLastError = true)]
    static extern bool WritePrinter(IntPtr hPrinter, byte[] bytes, int count, out int written);

    public static void Send(string printerName, string filePath)
    {
        byte[] bytes = File.ReadAllBytes(filePath);
        IntPtr printer;
        if (!OpenPrinter(printerName, out printer, IntPtr.Zero))
            throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "No se pudo abrir la impresora " + printerName);

        try
        {
            var info = new DOC_INFO_1 { pDocName = "Ticket de venta", pDataType = "RAW" };
            if (StartDocPrinter(printer, 1, info) == 0)
                throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "No se pudo iniciar el documento");
            try
            {
                if (!StartPagePrinter(printer))
                    throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "No se pudo iniciar la pagina");
                try
                {
                    int written;
                    if (!WritePrinter(printer, bytes, bytes.Length, out written) || written != bytes.Length)
                        throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "No se pudo enviar todo el ticket");
                }
                finally { EndPagePrinter(printer); }
            }
            finally { EndDocPrinter(printer); }
        }
        finally { ClosePrinter(printer); }
    }
}
'@

$queue = Get-Printer | Where-Object { $_.Name -eq $PrinterName }
if (!$queue) { throw 'La impresora solicitada no esta instalada en este equipo.' }
Add-Type -TypeDefinition $source -Language CSharp
[RawPrinter]::Send($PrinterName, $FilePath)
