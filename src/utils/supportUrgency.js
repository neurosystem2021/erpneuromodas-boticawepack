export const urgentReminderMs = 60000;
export function pendingUrgentTickets(rows) {
 return (rows || []).filter(t => t.priority === 'critical' && ['new', 'assigned', 'reopened', 'escalated'].includes(t.status));
}
export function urgentAnnouncement(total, ticket) {
 return total === 1 ? 'Tienes un ticket urgente pendiente de aceptar. ' + ticket.code + '. Revisa el centro de soporte.' : 'Tienes ' + total + ' tickets urgentes pendientes de aceptar. Revisa el centro de soporte.';
}
