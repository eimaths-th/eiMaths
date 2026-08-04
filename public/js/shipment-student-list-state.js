export function BuildShipmentStudentListStateOptions(shipment_id) {
    const has_shipment_context = typeof shipment_id === 'string'
        && shipment_id.trim() !== '';

    if (!has_shipment_context) {
        return {
            stateSave: true,
        };
    }

    return {
        stateSave: false,
        displayStart: 0,
    };
}
