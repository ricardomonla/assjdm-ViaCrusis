/**
 * Configuración de videos de YouTube del VíaCrucis 2025
 * Mapeo de escenas a video ID y timestamp de inicio
 */

const ESCENAS_YOUTUBE = [
  // === GRUPO 0XX - Intro / Previa ===
  // Video: https://youtu.be/0nxVUTRmb_w
  { id: "000", nombre: "Música / Ambiente", videoId: "0nxVUTRmb_w", timestamp: 0 },
  { id: "001", nombre: "Desfile - Pueblo", videoId: "0nxVUTRmb_w", timestamp: 175 },
  { id: "002", nombre: "Desfile - Apóstoles", videoId: "0nxVUTRmb_w", timestamp: 368 },
  { id: "003", nombre: "Desfile - Pilatos y Soldados", videoId: "0nxVUTRmb_w", timestamp: 596 },
  { id: "004", nombre: "Desfile - Curas", videoId: "0nxVUTRmb_w", timestamp: 859 },
  { id: "005", nombre: "Desfile - Herodes y Bailarinas", videoId: "0nxVUTRmb_w", timestamp: 956 },
  { id: "006", nombre: "Desfile - Diablo Suspenso", videoId: "0nxVUTRmb_w", timestamp: 1266 },

  // === GRUPO 1XX - Primera Parte ===
  // Video: https://youtu.be/ktDtijJMfbo
  { id: "101", nombre: "La entrada de Jesús en Jerusalén", videoId: "ktDtijJMfbo", timestamp: 0 },
  { id: "102", nombre: "El Trato de Judas y Caifás", videoId: "ktDtijJMfbo", timestamp: 182 },
  { id: "103", nombre: "La Última Cena", videoId: "ktDtijJMfbo", timestamp: 289 },
  { id: "104", nombre: "La Oración en el Huerto", videoId: "ktDtijJMfbo", timestamp: 873 },
  { id: "105", nombre: "La Detención de Jesús", videoId: "ktDtijJMfbo", timestamp: 1193 },
  { id: "106", nombre: "El Juicio de Jesús ante Caifás", videoId: "ktDtijJMfbo", timestamp: 1273 },
  { id: "107", nombre: "La Negación de Pedro", videoId: "ktDtijJMfbo", timestamp: 1333 },
  { id: "108", nombre: "El Juicio de Jesús ante Pilatos", videoId: "ktDtijJMfbo", timestamp: 1546 },
  { id: "109", nombre: "La Flagelación", videoId: "ktDtijJMfbo", timestamp: 1709 },
  { id: "110", nombre: "La Coronación de Espinas", videoId: "ktDtijJMfbo", timestamp: 2007 },
  { id: "111", nombre: "El Camino al Calvario", videoId: "ktDtijJMfbo", timestamp: 2422 },

  // === GRUPO 2XX - Segunda Parte ===
  // Video: https://youtu.be/GPZE-uxt0LQ
  { id: "201", nombre: "Primera Caída", videoId: "GPZE-uxt0LQ", timestamp: 0 },
  { id: "202", nombre: "Encuentro con la Virgen", videoId: "GPZE-uxt0LQ", timestamp: 546 },
  { id: "203", nombre: "Encuentro con Verónica", videoId: "GPZE-uxt0LQ", timestamp: 885 },
  { id: "204", nombre: "Segunda Caída", videoId: "GPZE-uxt0LQ", timestamp: 1267 },
  { id: "205", nombre: "Encuentro con las Mujeres de Jerusalén", videoId: "GPZE-uxt0LQ", timestamp: 1464 },
  { id: "206", nombre: "Tercera Caída", videoId: "GPZE-uxt0LQ", timestamp: 1772 },
  { id: "207", nombre: "La Desnudez de Jesús", videoId: "GPZE-uxt0LQ", timestamp: 1952 },

  // === GRUPO 3XX - Tercera Parte ===
  // Video: https://youtu.be/a0LB3VWQstw
  { id: "301", nombre: "La Crucifixión", videoId: "a0LB3VWQstw", timestamp: 0 },
  { id: "302", nombre: "La Muerte de Jesús", videoId: "a0LB3VWQstw", timestamp: 131 },
  { id: "303", nombre: "El Descendimiento", videoId: "a0LB3VWQstw", timestamp: 173 },
  { id: "304", nombre: "El Entierro", videoId: "a0LB3VWQstw", timestamp: 728 },
  { id: "305", nombre: "La Resurrección", videoId: "a0LB3VWQstw", timestamp: 814 },
  { id: "306", nombre: "Final / Reflexión", videoId: "a0LB3VWQstw", timestamp: 923 }
];

// Grupos de videos para referencia
const VIDEOS_YOUTUBE = {
  "0XX": { videoId: "0nxVUTRmb_w", nombre: "Intro / Previa" },
  "1XX": { videoId: "ktDtijJMfbo", nombre: "Primera Parte" },
  "2XX": { videoId: "GPZE-uxt0LQ", nombre: "Segunda Parte" },
  "3XX": { videoId: "a0LB3VWQstw", nombre: "Tercera Parte" }
};
