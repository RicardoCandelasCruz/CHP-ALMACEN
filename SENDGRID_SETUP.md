# Configuración de SendGrid para Railway

## Pasos para configurar SendGrid:

### 1. Crear cuenta en SendGrid
- Ve a https://sendgrid.com/
- Regístrate (plan gratuito: 100 emails/día)
- Verifica tu email

### 2. Crear API Key
- Ve a Settings > API Keys
- Click "Create API Key"
- Nombre: `CHP-ALMACEN-Railway`
- Permisos: `Full Access` o `Mail Send`
- Copia la API Key (empieza con `SG.`)

### 3. Verificar dominio/email (Importante)
- Ve a Settings > Sender Authentication
- Verifica tu email `cheesepizzarecepcion@gmail.com`
- O configura un dominio propio

### 4. Configurar en Railway
En tu proyecto Railway, ve a Variables y agrega:
```
SENDGRID_API_KEY=SG.tu_api_key_completa_aqui
```

### 5. Verificar configuración
El sistema automáticamente detectará la API key y usará SendGrid.

## Verificación de funcionamiento:
1. Haz un pedido de prueba
2. Revisa los logs de Railway
3. Deberías ver: "Correo enviado exitosamente con SendGrid para pedido #X"

## Troubleshooting:
- Si falla, revisa que el email esté verificado en SendGrid
- Verifica que la API key tenga permisos de Mail Send
- Revisa los logs de Railway para errores específicos