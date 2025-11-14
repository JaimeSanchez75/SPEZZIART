import swaggerJSDoc, { OAS3Definition, OAS3Options } from "swagger-jsdoc";

const swaggerDefinition: OAS3Definition = {
	openapi: "3.0.0",
	info: {
		title: "Alimentos API",
		version: "1.0.0",
		"description": "La Alimentos API es una API que permite obtener información nutricional de diferentes alimentos.\n\n_Entre los nutrientes que se pueden obtener se incluyen: Kcal, agua, hierro, vitamina B5, grasa animal y vegetal, proteína vegetal y animal, entre otros._\n\nEnlaces utiles:\n- [Repositorio API en Github](https://github.com/NBCharro/apiNutricion)\n- [Github](https://github.com/NBCharro)\n- [Portfolio](https://www.nbcharro.com/)\n- [Linkedin](https://www.linkedin.com/in/nbcharro/)",
	},
	servers: [
		{
			url: "https://api-nutricion.onrender.com/api/v1/",
		},
	],
	components: {
		schemas: {
			alimento: {
				type: "object",
				required: ["id", "alimento", "poderCalorifico", "kcal", "agua", "carboHidratos", "colesterol", "fibra", "proteina", "lipidos", "minerales", "vitaminas"],
				properties: {
					id: {
						type: "integer",
						format: "int64",
						example: 1
					},
					alimento: {
						type: "string",
						example: "aceite de coco",
					},
					poderCalorifico: {
						type: "integer",
						format: "int64",
						example: 100,
					},
					kcal: {
						type: "integer",
						format: "int64",
						example: 900,
					},
					agua:
					{
						type: "integer",
						format: "int64",
						example: 0.001,
					},
					carboHidratos:
					{
						type: "integer",
						format: "int64",
						example: 0,
					},
					colesterol: {
						type: "integer",
						format: "int64",
						example: 0,
					},
					fibra:
					{
						type: "integer",
						format: "int64",
						example: 0,
					},
					proteina:
					{
						type: "object",
						required: ["animal", "vegetal"],
						properties:
						{
							animal: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							vegetal: {
								type: "integer",
								format: "int64",
								example: 0,
							}
						},
					},
					lipidos:
					{
						type: "object",
						required: ["grasa", "saturada", "monoinsaturada", "poliinsaturada"],
						properties:
						{
							grasa: {
								type: "integer",
								format: "int64",
								example: 100,
							},
							saturada: {
								type: "integer",
								format: "int64",
								example: 85.5,
							},
							monoinsaturada: {
								type: "integer",
								format: "int64",
								example: 5.8,
							},
							poliinsaturada: {
								type: "integer",
								format: "int64",
								example: 5.8,
							}
						},
					},
					minerales:
					{
						type: "object",
						required: ["calcio", "potasio", "fosforo", "cobre", "yodo", "selenio", "cloro", "hierro", "sodio", "zinq", "magnesio", "manganeso"],
						properties:
						{
							calcio: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							potasio: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							fosforo: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							cobre: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							yodo: {
								type: "integer",
								format: "int64",
								example: null,
							},
							selenio: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							cloro: {
								type: "integer",
								format: "int64",
								example: null,
							},
							hierro: {
								type: "integer",
								format: "int64",
								example: 0.04,
							},
							sodio: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							zinq: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							magnesio: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							manganeso: {
								type: "integer",
								format: "int64",
								example: 0,
							}
						},
					},
					vitaminas:
					{
						type: "object",
						required: ["b1", "b2", "b3", "b5", "b6", "b8", "b9", "b12", "c", "d", "a", "e"],
						properties:
						{
							b1: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b2: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b3: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b5: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b6: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b8: {
								type: "integer",
								format: "int64",
								example: null,
							},
							b9: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b12: {
								type: "integer",
								format: "int64",
								example: null,
							},
							c: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							d: {
								type: "integer",
								format: "int64",
								example: null,
							},
							a: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							e: {
								type: "integer",
								format: "int64",
								example: 0.28,
							}
						}
					},
				},
			},
			listaAlimentos: {
				type: "array",
				example: ["aceite de coco", "aceite de girasol", "aceite de oliva", "aceite de palma", "aceituna negra"],
			},
			mayor: {
				type: "object",
				required: ["id", "alimento", "poderCalorifico", "kcal", "agua", "carboHidratos", "colesterol", "fibra", "proteina", "lipidos", "minerales", "vitaminas"],
				properties: {
					id: {
						type: "integer",
						format: "int64",
						example: 79
					},
					alimento: {
						type: "string",
						example: "cereales all bran plus",
					},
					poderCalorifico: {
						type: "integer",
						format: "int64",
						example: 100,
					},
					kcal: {
						type: "integer",
						format: "int64",
						example: 265,
					},
					agua:
					{
						type: "integer",
						format: "int64",
						example: 9,
					},
					carboHidratos:
					{
						type: "integer",
						format: "int64",
						example: 46,
					},
					colesterol: {
						type: "integer",
						format: "int64",
						example: 0,
					},
					fibra:
					{
						type: "integer",
						format: "int64",
						example: 29,
					},
					proteina:
					{
						type: "object",
						required: ["animal", "vegetal"],
						properties:
						{
							animal: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							vegetal: {
								type: "integer",
								format: "int64",
								example: 13,
							}
						},
					},
					lipidos:
					{
						type: "object",
						required: ["grasa", "saturada", "monoinsaturada", "poliinsaturada"],
						properties:
						{
							grasa: {
								type: "integer",
								format: "int64",
								example: 4.5,
							},
							saturada: {
								type: "integer",
								format: "int64",
								example: 0.5,
							},
							monoinsaturada: {
								type: "integer",
								format: "int64",
								example: null,
							},
							poliinsaturada: {
								type: "integer",
								format: "int64",
								example: null,
							}
						},
					},
					minerales:
					{
						type: "object",
						required: ["calcio", "potasio", "fosforo", "cobre", "yodo", "selenio", "cloro", "hierro", "sodio", "zinq", "magnesio", "manganeso"],
						properties:
						{
							calcio: {
								type: "integer",
								format: "int64",
								example: 8.8,
							},
							potasio: {
								type: "integer",
								format: "int64",
								example: null,
							},
							fosforo: {
								type: "integer",
								format: "int64",
								example: null,
							},
							cobre: {
								type: "integer",
								format: "int64",
								example: null,
							},
							yodo: {
								type: "integer",
								format: "int64",
								example: null,
							},
							selenio: {
								type: "integer",
								format: "int64",
								example: null,
							},
							cloro: {
								type: "integer",
								format: "int64",
								example: null,
							},
							hierro: {
								type: "integer",
								format: "int64",
								example: 340,
							},
							sodio: {
								type: "integer",
								format: "int64",
								example: 800,
							},
							zinq: {
								type: "integer",
								format: "int64",
								example: null,
							},
							magnesio: {
								type: "integer",
								format: "int64",
								example: null,
							},
							manganeso: {
								type: "integer",
								format: "int64",
								example: null,
							}
						},
					},
					vitaminas:
					{
						type: "object",
						required: ["b1", "b2", "b3", "b5", "b6", "b8", "b9", "b12", "c", "d", "a", "e"],
						properties:
						{
							b1: {
								type: "integer",
								format: "int64",
								example: 0.9,
							},
							b2: {
								type: "integer",
								format: "int64",
								example: 1,
							},
							b3: {
								type: "integer",
								format: "int64",
								example: 11.3,
							},
							b5: {
								type: "integer",
								format: "int64",
								example: null,
							},
							b6: {
								type: "integer",
								format: "int64",
								example: 1.3,
							},
							b8: {
								type: "integer",
								format: "int64",
								example: null,
							},
							b9: {
								type: "integer",
								format: "int64",
								example: 250,
							},
							b12: {
								type: "integer",
								format: "int64",
								example: 0.6,
							},
							c: {
								type: "integer",
								format: "int64",
								example: null,
							},
							d: {
								type: "integer",
								format: "int64",
								example: 3.1,
							},
							a: {
								type: "integer",
								format: "int64",
								example: null,
							},
							e: {
								type: "integer",
								format: "int64",
								example: null,
							}
						}
					},
				},
			},
			menor: {
				type: "object",
				required: ["id", "alimento", "poderCalorifico", "kcal", "agua", "carboHidratos", "colesterol", "fibra", "proteina", "lipidos", "minerales", "vitaminas"],
				properties: {
					id: {
						type: "integer",
						format: "int64",
						example: 34
					},
					alimento: {
						type: "string",
						example: "azucar",
					},
					poderCalorifico: {
						type: "integer",
						format: "int64",
						example: 100,
					},
					kcal: {
						type: "integer",
						format: "int64",
						example: 374,
					},
					agua:
					{
						type: "integer",
						format: "int64",
						example: 0,
					},
					carboHidratos:
					{
						type: "integer",
						format: "int64",
						example: 99.8,
					},
					colesterol: {
						type: "integer",
						format: "int64",
						example: 0,
					},
					fibra:
					{
						type: "integer",
						format: "int64",
						example: 0,
					},
					proteina:
					{
						type: "object",
						required: ["animal", "vegetal"],
						properties:
						{
							animal: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							vegetal: {
								type: "integer",
								format: "int64",
								example: 0,
							}
						},
					},
					lipidos:
					{
						type: "object",
						required: ["grasa", "saturada", "monoinsaturada", "poliinsaturada"],
						properties:
						{
							grasa: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							saturada: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							monoinsaturada: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							poliinsaturada: {
								type: "integer",
								format: "int64",
								example: 0,
							}
						},
					},
					minerales:
					{
						type: "object",
						required: ["calcio", "potasio", "fosforo", "cobre", "yodo", "selenio", "cloro", "hierro", "sodio", "zinq", "magnesio", "manganeso"],
						properties:
						{
							calcio: {
								type: "integer",
								format: "int64",
								example: 0.6,
							},
							potasio: {
								type: "integer",
								format: "int64",
								example: 2.2,
							},
							fosforo: {
								type: "integer",
								format: "int64",
								example: 0.3,
							},
							cobre: {
								type: "integer",
								format: "int64",
								example: 0.02,
							},
							yodo: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							selenio: {
								type: "integer",
								format: "int64",
								example: 0.001,
							},
							cloro: {
								type: "integer",
								format: "int64",
								example: 0.001,
							},
							hierro: {
								type: "integer",
								format: "int64",
								example: 0.29,
							},
							sodio: {
								type: "integer",
								format: "int64",
								example: 0.3,
							},
							zinq: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							magnesio: {
								type: "integer",
								format: "int64",
								example: 0.2,
							},
							manganeso: {
								type: "integer",
								format: "int64",
								example: 0.001,
							}
						},
					},
					vitaminas:
					{
						type: "object",
						required: ["b1", "b2", "b3", "b5", "b6", "b8", "b9", "b12", "c", "d", "a", "e"],
						properties:
						{
							b1: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b2: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b3: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b5: {
								type: "integer",
								format: "int64",
								example: null,
							},
							b6: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b8: {
								type: "integer",
								format: "int64",
								example: null,
							},
							b9: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							b12: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							c: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							d: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							a: {
								type: "integer",
								format: "int64",
								example: 0,
							},
							e: {
								type: "integer",
								format: "int64",
								example: 0,
							}
						}
					},
				},
			},
		},
	},
};

const swaggerOptions: OAS3Options = {
	swaggerDefinition,
	// Busca los comentarios de las rutas para crear la documentacion
	apis: ["./src/routes/*.ts"],
};

export default swaggerJSDoc(swaggerOptions);