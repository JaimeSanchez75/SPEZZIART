"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.obtenerMenor = exports.obtenerMayor = exports.obtenerAlimentosPorNombre = exports.obtenerDatosPorID = exports.obtenerlistaAlimentos = exports.obtenerTodosDatos = void 0;
const datosAlimentos_json_1 = __importDefault(require("./datosAlimentos.json"));
const alimentos = datosAlimentos_json_1.default;
const obtenerTodosDatos = () => alimentos;
exports.obtenerTodosDatos = obtenerTodosDatos;
const obtenerlistaAlimentos = () => {
    let listaNombresAlimentos = [];
    alimentos.forEach(alimento => {
        listaNombresAlimentos.push(alimento.alimento);
    });
    return listaNombresAlimentos;
};
exports.obtenerlistaAlimentos = obtenerlistaAlimentos;
const obtenerDatosPorID = (id) => {
    const alimento = alimentos.find(d => d.id == id);
    return alimento;
};
exports.obtenerDatosPorID = obtenerDatosPorID;
const obtenerAlimentosPorNombre = (nombreAlimento) => {
    let alimento = [];
    alimentos.forEach(element => {
        if (element.alimento == nombreAlimento) {
            alimento.push(element);
        }
        //  else if (element.alimento.includes(nombreAlimento)) {
        // 	alimento.push(element);
        // }
    });
    return alimento;
};
exports.obtenerAlimentosPorNombre = obtenerAlimentosPorNombre;
const obtenerMayor = (caracteristica) => {
    let alimento = [];
    let mayorValor = null;
    alimentos.forEach(element => {
        const valorCaracteristica = eval(`element.${caracteristica}`) ||
            eval(`element.lipidos.${caracteristica}`) ||
            eval(`element.minerales.${caracteristica}`) ||
            eval(`element.vitaminas.${caracteristica}`);
        if (valorCaracteristica != null && (mayorValor === null || valorCaracteristica > mayorValor)) {
            // Si no es nulo y es mayor, sustituye el alimento y el valor
            mayorValor = valorCaracteristica;
            alimento = element;
        }
        else if (valorCaracteristica != null && valorCaracteristica === mayorValor) {
            // Si no es nulo y es igual, añade el alimento al array
            if (!Array.isArray(alimento)) {
                // Si no es un array, lo convierte en array
                alimento = [alimento];
            }
            alimento.push(element);
        }
    });
    return alimento;
};
exports.obtenerMayor = obtenerMayor;
const obtenerMenor = (caracteristica) => {
    let alimento = [];
    let menorValor = null;
    alimentos.forEach(element => {
        const currentCaracteristicaValue = eval(`element.${caracteristica}`) ||
            eval(`element.lipidos.${caracteristica}`) ||
            eval(`element.minerales.${caracteristica}`) ||
            eval(`element.vitaminas.${caracteristica}`);
        if (currentCaracteristicaValue != null && (menorValor === null || currentCaracteristicaValue < menorValor)) {
            // Si no es nulo y es menor, sustituye el alimento y el valor
            menorValor = currentCaracteristicaValue;
            alimento = element;
        }
        else if (currentCaracteristicaValue != null && currentCaracteristicaValue === menorValor) {
            // Si no es nulo y es igual, añade el alimento al array
            if (!Array.isArray(alimento)) {
                // Si no es un array, lo convierte en array
                alimento = [alimento];
            }
            alimento.push(element);
        }
    });
    return alimento;
};
exports.obtenerMenor = obtenerMenor;
