export interface Alimento {
	id: number,
	alimento: string,
	poderCalorifico: number,
	kcal: number,
	agua: number | null,
	carboHidratos: number | null,
	colesterol: number | null,
	fibra: number | null,
	proteina: {
		animal: number | null,
		vegetal: number | null
	},
	lipidos: {
		grasa: number | null,
		saturada: number | null,
		monoinsaturada: number | null,
		poliinsaturada: number | null
	},
	minerales: {
		calcio: number | null,
		potasio: number | null,
		fosforo: number | null,
		cobre: number | null,
		yodo: number | null,
		selenio: number | null,
		cloro: number | null,
		hierro: number | null,
		sodio: number | null,
		zinq: number | null,
		magnesio: number | null,
		manganeso: number | null
	},
	vitaminas: {
		b1: number | null,
		b2: number | null,
		b3: number | null,
		b5: number | null,
		b6: number | null,
		b8: number | null,
		b9: number | null,
		b12: number | null,
		c: number | null,
		d: number | null,
		a: number | null,
		e: number | null
	}
}

