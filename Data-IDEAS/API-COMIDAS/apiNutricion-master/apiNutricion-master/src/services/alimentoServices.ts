import { Alimento } from '../types'
import datosAlimentos from './datosAlimentos.json'

const alimentos: Alimento[] = datosAlimentos as Alimento[]

export const obtenerTodosDatos = (): Alimento[] => alimentos

export const obtenerlistaAlimentos = (): string[] => {
	let listaNombresAlimentos: string[] = [];
	alimentos.forEach(alimento => {
		listaNombresAlimentos.push(alimento.alimento);
	});
	return listaNombresAlimentos;
}

export const obtenerDatosPorID = (id: number): Alimento | undefined => {
	const alimento = alimentos.find(d => d.id == id)
	return alimento
}

export const obtenerAlimentosPorNombre = (nombreAlimento: string): Alimento[] => {
	let alimento: Alimento[] = [];
	alimentos.forEach(element => {
		if (element.alimento == nombreAlimento) {
			alimento.push(element);
		}
		//  else if (element.alimento.includes(nombreAlimento)) {
		// 	alimento.push(element);
		// }
	});
	return alimento;
}

export const obtenerMayor = (caracteristica: string): Alimento | Alimento[] => {
	let alimento: Alimento | Alimento[] = [];
	let mayorValor: number | null = null;

	alimentos.forEach(element => {
		const valorCaracteristica = eval(`element.${caracteristica}`) ||
			eval(`element.lipidos.${caracteristica}`) ||
			eval(`element.minerales.${caracteristica}`) ||
			eval(`element.vitaminas.${caracteristica}`);

		if (valorCaracteristica != null && (mayorValor === null || valorCaracteristica > mayorValor)) {
			// Si no es nulo y es mayor, sustituye el alimento y el valor
			mayorValor = valorCaracteristica;
			alimento = element;
		} else if (valorCaracteristica != null && valorCaracteristica === mayorValor) {
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

export const obtenerMenor = (caracteristica: string): Alimento | Alimento[] => {
	let alimento: Alimento | Alimento[] = [];
	let menorValor: number | null = null;

	alimentos.forEach(element => {
		const currentCaracteristicaValue = eval(`element.${caracteristica}`) ||
			eval(`element.lipidos.${caracteristica}`) ||
			eval(`element.minerales.${caracteristica}`) ||
			eval(`element.vitaminas.${caracteristica}`);

		if (currentCaracteristicaValue != null && (menorValor === null || currentCaracteristicaValue < menorValor)) {
			// Si no es nulo y es menor, sustituye el alimento y el valor
			menorValor = currentCaracteristicaValue;
			alimento = element;
		} else if (currentCaracteristicaValue != null && currentCaracteristicaValue === menorValor) {
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


