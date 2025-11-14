import express from 'express';
import swaggerUI from 'swagger-ui-express';
import swaggerSetup from './docs/swagger';
import alimentosRouter from './routes/alimentos'

const app = express()
app.use(express.json())
const cors = require('cors');
app.use(cors());
const PORT = process.env.PORT || 3000


// Funcionalidad de la API
app.use('/api/v1/', alimentosRouter)

// Documentacion de la API
app.use('/', swaggerUI.serve, swaggerUI.setup(swaggerSetup));

app.listen(PORT, () => {
	console.log(`Server is running in port ${PORT}`);
})

