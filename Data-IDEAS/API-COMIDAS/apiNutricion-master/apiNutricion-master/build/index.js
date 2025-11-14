"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const swagger_ui_express_1 = __importDefault(require("swagger-ui-express"));
const swagger_1 = __importDefault(require("./docs/swagger"));
const alimentos_1 = __importDefault(require("./routes/alimentos"));
const app = (0, express_1.default)();
app.use(express_1.default.json());
const cors = require('cors');
app.use(cors());
const PORT = process.env.PORT || 3000;
// Funcionalidad de la API
app.use('/api/v1/', alimentos_1.default);
// Documentacion de la API
app.use('/', swagger_ui_express_1.default.serve, swagger_ui_express_1.default.setup(swagger_1.default));
app.listen(PORT, () => {
    console.log(`Server is running in port ${PORT}`);
});
