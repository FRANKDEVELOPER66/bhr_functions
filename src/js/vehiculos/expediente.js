export const generarExpediente = (placa) => {
    window.open(`${BASE}/vehiculos/expediente?placa=${encodeURIComponent(placa)}`, '_blank');
};
 
// Exponer globalmente (type="module")
window.generarExpediente = generarExpediente;
 