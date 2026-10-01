# HCB Salud

Sistema interno de gestión clínica construido con Laravel 12, PHP 8.2, Blade y SQLite. Incluye pacientes, agenda, equipo clínico, consultas, signos vitales, prescripciones y perfiles de acceso.

## Requisitos

- PHP 8.2 o superior con extensiones `openssl`, `pdo_sqlite`, `mbstring`, `fileinfo` y `xml`.
- Composer 2.
- Node.js 20.19+ y npm para compilar los recursos web.

## Inicio local en Windows

Desde la carpeta del proyecto, en PowerShell:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan clinic:user
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

`clinic:user` solicita el nombre, correo, rol y una contraseña de al menos 12 caracteres. El primer usuario se propone como administrador. No existen contraseñas predeterminadas.

Los pacientes pueden crear una solicitud de cuenta en `/register`. El acceso queda pendiente hasta que un administrador revise la identidad y la vincule a un expediente activo que coincida en tipo/número de documento y fecha de nacimiento. La aprobación se realiza en **Accesos pendientes**. Después, el paciente puede ver y solicitar citas propias; el portal no muestra notas ni antecedentes clínicos.

Para desarrollo con recarga de estilos, ejecuta `npm run dev` en otra terminal en lugar de `npm run build`.

El seeder agrega pacientes, profesionales y citas de demostración ficticios. Para trabajar con una base de datos nueva, crea `database/database.sqlite` antes de ejecutar las migraciones. SQLite es la configuración inicial; actualiza las variables `DB_*` de `.env` si vas a conectar MySQL en XAMPP.

## Accesos

- `admin`: gestión general y borrado de cuentas.
- `doctor`: consulta de expedientes y registro de atenciones.
- `nurse`: apoyo a pacientes, agenda y registro clínico.
- `receptionist`: expedientes y agenda, sin acceso de escritura a notas clínicas.
- `patient`: portal personal, habilitado después de la verificación administrativa.

Las cuentas internas se crean desde consola con `php artisan clinic:user`; los perfiles clínicos se administran desde el directorio. El acceso a pacientes y notas requiere autenticación y rol permitido. Los médicos solo ven pacientes asignados a su perfil y recepción no puede leer antecedentes ni consultas. Las citas del mismo profesional no pueden solaparse; la atención guarda la consulta, sus medicamentos y el cambio de estado dentro de una transacción.

## Pruebas

```powershell
php artisan test
npm run build
```

## Consideraciones de privacidad

Los datos del seeder son ficticios. Esta base de proyecto no constituye por sí sola una certificación clínica ni de cumplimiento normativo. Antes de usar datos reales en producción, configura HTTPS, copias de seguridad cifradas, gestión de secretos, retención y auditoría, control de sesión, correo seguro y los requisitos legales de protección de datos aplicables. No uses información real de pacientes en el entorno de demostración.