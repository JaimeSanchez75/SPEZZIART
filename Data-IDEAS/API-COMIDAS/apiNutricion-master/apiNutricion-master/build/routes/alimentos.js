"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const alimentoServices = __importStar(require("../services/alimentoServices"));
const router = express_1.default.Router();
/**
 * @openapi
 * /api/v1:
 *   get:
 *     summary: Obtiene todos los datos de la API
 *     description: Devuelve todos los datos disponibles
 *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             $ref: "#/components/schemas/alimento"
 *     responses:
 *       '200':
 *         description: Success
 */
router.get('/', (_req, res) => {
    res.status(200).send(alimentoServices.obtenerTodosDatos());
});
/**
 * @openapi
 * /api/v1/lista:
 *   get:
 *     summary: Obtener lista de los alimentos
 *     description: Devuelve una lista con todos los alimentos de la API
 *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             $ref: "#/components/schemas/listaAlimentos"
 *     responses:
 *       '200':
 *         description: Success
 */
router.get('/lista', (_req, res) => {
    res.status(200).send(alimentoServices.obtenerlistaAlimentos());
});
/**
 * @openapi
 * /api/v1/id={id}:
 *   get:
 *     summary: Obtener datos de un alimento por su ID
 *     description: Devuelve los datos de un alimento por su ID
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         description: ID del alimento
 *         schema:
 *           type: integer
 *           format: int64
 *           example: 1
 *     responses:
 *       '200':
 *         description: Success
 *       '404':
 *         description: Alimento no encontrado
 */
router.get('/id=:id', (req, res) => {
    const alimento = alimentoServices.obtenerDatosPorID(+req.params.id);
    return alimento ?
        res.send(alimento) : res.status(404).send({ message: "Alimento no encontrado" });
});
/**
 * @openapi
 * /api/v1/nombre={nombreAlimento}:
 *   get:
 *     summary: Obtener alimentos por nombre
 *     description: Permite buscar alimentos por nombre
  *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             $ref: "#/components/schemas/alimento"
 *     parameters:
 *       - in: path
 *         name: nombreAlimento
 *         required: true
 *         description: Nombre del alimento
 *         schema:
 *           type: string
 *           example: aceite de coco
 *     responses:
 *       '200':
 *         description: Success
 *       '404':
 *         description: Alimento no encontrado
 */
router.get('/nombre=:nombreAlimento', (req, res) => {
    const alimento = alimentoServices.obtenerAlimentosPorNombre(req.params.nombreAlimento);
    return alimento && alimento.length >= 1 ?
        res.send(alimento) : res.status(404).send({ message: "Alimento no encontrado" });
});
/**
 * @openapi
 * /api/v1/mayor/{caracteristica}:
 *   get:
 *     summary: Obtener mayor
 *     description: Permite buscar el alimento con mayor cantidad de una característica específica
  *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             $ref: "#/components/schemas/mayor"
 *     parameters:
 *       - in: path
 *         name: caracteristica
 *         required: true
 *         description: Característica del alimento
 *         schema:
 *           type: string
 *           example: hierro
 *     responses:
 *       '200':
 *         description: Success
 *       '404':
 *         description: Alimento no encontrado
 */
router.get('/mayor/:caracteristica', (req, res) => {
    const alimento = alimentoServices.obtenerMayor(req.params.caracteristica);
    return alimento ?
        res.send(alimento) : res.status(404).send({ message: "Alimento no encontrado" });
});
/**
 * @openapi
 * /api/v1/menor/{caracteristica}:
 *   get:
 *     summary: Obtener menor
 *     description: Permite buscar el alimento con menor cantidad de una característica específica
  *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             $ref: "#/components/schemas/menor"
 *     parameters:
 *       - in: path
 *         name: caracteristica
 *         required: true
 *         description: Característica del alimento
 *         schema:
 *           type: string
 *           example: monoinsaturada
 *     responses:
 *       '200':
 *         description: Success
 *       '404':
 *         description: Alimento no encontrado
 */
router.get('/menor/:caracteristica', (req, res) => {
    const alimento = alimentoServices.obtenerMenor(req.params.caracteristica);
    return alimento ?
        res.send(alimento) : res.status(404).send({ message: "Alimento no encontrado" });
});
exports.default = router;
