Guarda aqui las fotos reales de DENS32.

Sugerencia de archivos para la estructura actual:

- `hero-primary.jpg`: primera foto vertical de la clínica para la portada
- `hero-secondary.jpg`: segunda foto vertical de la clínica para la portada
- `about-team.jpg`: foto del equipo para `Sobre nosotros`

Luego abre `src/app/config/siteContent.ts` y reemplaza las URLs del objeto `photos`
por imports locales, por ejemplo:

```ts
import heroPrimary from '@/assets/images/hero-primary.jpg'
import heroSecondary from '@/assets/images/hero-secondary.jpg'
import aboutTeam from '@/assets/images/about-team.jpg'

photos: {
  heroPrimary,
  heroSecondary,
  aboutTeam,
}
```
