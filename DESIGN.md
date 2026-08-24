---
name: AranduTech Ventas
description: POS y gestión para comercios de Paraguay — la caja no espera
colors:
  primary: "#4f46e5"
  primary-hover: "#4338ca"
  landing: "#0f766e"
  ink: "#0f172a"
  muted: "#64748b"
  lead: "#475569"
  page: "#f1f5f9"
  surface: "#ffffff"
  surface-2: "#f8fafc"
  line: "#e2e8f0"
  success: "#059669"
  warning: "#d97706"
  danger: "#dc2626"
  info: "#0284c7"
  on-accent: "#ffffff"
typography:
  display:
    fontFamily: "Outfit, system-ui, sans-serif"
    fontSize: "clamp(2rem, 4vw, 3.2rem)"
    fontWeight: 700
    lineHeight: 1.15
    letterSpacing: "-0.03em"
  headline:
    fontFamily: "Outfit, system-ui, sans-serif"
    fontSize: "1.75rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.03em"
  title:
    fontFamily: "Outfit, system-ui, sans-serif"
    fontSize: "1.15rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "normal"
  body:
    fontFamily: "Outfit, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Outfit, system-ui, sans-serif"
    fontSize: "0.82rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.04em"
rounded:
  sm: "8px"
  md: "12px"
  lg: "16px"
  pill: "999px"
spacing:
  sm: "8px"
  md: "16px"
  lg: "24px"
  gutter: "20px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-accent}"
    rounded: "{rounded.sm}"
    padding: "0.55rem 1.25rem"
    typography: "{typography.title}"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
    textColor: "{colors.on-accent}"
    rounded: "{rounded.sm}"
    padding: "0.55rem 1.25rem"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "2.75rem"
    padding: "0.55rem 0.85rem"
  input-focus:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "2.75rem"
    padding: "0.55rem 0.85rem"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.md}"
    padding: "1.25rem"
  nav-dark:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.on-accent}"
    padding: "0.45rem 0.8rem"
  badge:
    backgroundColor: "{colors.surface-2}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    padding: "0.15rem 0.55rem"
    typography: "{typography.label}"
  landing-cta:
    backgroundColor: "{colors.landing}"
    textColor: "{colors.on-accent}"
    rounded: "10px"
    padding: "12px 20px"
    typography: "{typography.title}"
---

# Design System: AranduTech Ventas

## Overview

**Creative North Star: "La caja no espera"**

Sistema de gestión que se cobra en el mostrador. El look es el de un POS familiar (AdminLTE, Bootstrap 4) vestido con Outfit, índigo y densidad de escritorio: no es un marketing site ni un dashboard de métricas vacías. Caja y administración se sienten el mismo producto; la plataforma de staff usa la misma piel.

El acento del producto es el índigo. El teal de la landing y el CTA de WhatsApp no se copian al POS ni al panel staff. Logo y nombre salen de la configuración del comercio (`app_name`, `app_logo`); no hay paleta contractual aparte de esos assets y estos tokens.

Voz en pantalla: español rioplatense (“Ingresá”, “Usá tu correo”). Acciones primarias grandes, un paso, texto que no se parte.

**Key Characteristics:**
- Una cara (Outfit + índigo + slate) para POS y plataforma; teal solo en landing
- Densidad útil en pantallas anchas; no dejar un control de 360px perdido en 1800px
- Lo familiar de un sistema de gestión se queda; el adorno que no ayuda a cobrar, se va
- AdminLTE es el andamio, no la identidad: se pule look, no se rearma la navegación

## Colors

Índigo para actuar, slate para leer, semánticos para estado. El teal es de persuadir, no de operar.

### Primary
- **Índigo de caja** (`{colors.primary}`): botones primarios, ítem activo del nav, foco, selección de texto, caret. Es la voz de “hacer”. En hover/focus baja a `{colors.primary-hover}`.
- **Sobre acento** (`{colors.on-accent}`): texto e íconos encima de índigo, teal, success y danger.

### Secondary
- **Teal de landing** (`{colors.landing}`): CTA de WhatsApp y links de la landing pública. No usar en POS ni en `/plataforma`.

### Neutral
- **Tinta** (`{colors.ink}`): texto principal y barra de navegación oscura.
- **Apagado** (`{colors.muted}`): meta, placeholders, thead. En leads de página usar `{colors.lead}`.
- **Página** (`{colors.page}`): fondo de app (POS y plataforma). Landing usa `{colors.surface-2}` como fondo.
- **Superficie** (`{colors.surface}`): cards, inputs, paneles.
- **Superficie 2** (`{colors.surface-2}`): header de tabla, footer de card, badge neutro.
- **Línea** (`{colors.line}`): bordes y divisores.

### Named Rules
**La regla de una voz.** El índigo es el acento del producto. El teal queda en landing y WhatsApp.

**La regla del diez por ciento.** El índigo pinta controles y estado activo, no fondos de página ni bloques enteros.

## Typography

**Display Font:** Outfit (con system-ui)
**Body Font:** Outfit (con system-ui)
**Label/Mono Font:** Outfit; `tabular-nums` en montos Gs. `code` solo para slugs y nombres de base.

**Character:** Geométrica, compacta, de software de escritorio. El peso 700 carga el título; el 600 carga el control; el 400 lee.

### Hierarchy
- **Display** (700, `clamp(2rem, 4vw, 3.2rem)`, 1.15, tracking -0.03em): hero de landing. No en el POS.
- **Headline** (700, 1.75rem plataforma / 1.6rem POS, tracking -0.02em a -0.03em): título de página. Sin kicker ni eyebrow encima.
- **Title** (600, ~1.15rem): header de card, precio de plan, botón.
- **Body** (400, 1rem / 16px, hasta 17px desde 1600px): lectura. Medida de lead ~40–42rem.
- **Label** (600, 0.82rem, tracking 0.04em, mayúsculas): thead de tabla. Labels de form en 600, sentence case, no uppercase.

### Named Rules
**La regla de un paso.** El texto del botón de caja nombra la acción y no se parte (`white-space: nowrap`).

## Layout

POS: `layout-top-nav` AdminLTE, `container-fluid`, navbar fija. Plataforma: mismo andamio, contenido en un wrap de 1240px (`platform-shell`). Landing: max 1100px, hero + grilla de planes `auto-fit` / min 240px.

Ritmo: grupos juntos, separación generosa entre bloques. Más espacio arriba de un heading que abajo. Gutter 20px en landing; plataforma 1.25rem.

Breakpoints observados: colapso de nav y de stats a 2 columnas en 991.98px; stats a 1 columna y formularios a 1 columna en 575.98px. En 1600px el html sube a 17px.

Dark mode existe en el POS (`html.dark-mode`); la plataforma staff es light-only.

## Elevation & Depth

Híbrido: las cards viven levantadas con una sombra ambient; el resto es plano y tonal. No hay neobrutalismo ni halo de color a offset 0.

### Shadow Vocabulary
- **Card** (`box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08)`): cards de POS y plataforma. En dark mode del POS: `0 8px 24px rgba(0, 0, 0, 0.35)`.
- **Botón primario** (`0 1px 2px rgba(15, 23, 42, 0.12)`): apenas presente, no es un lift.
- **Stat hover del POS** (`translateY(-5px)` + sombra larga): solo `small-box` del comercio. La plataforma no levanta las stats.

### Named Rules
**La regla de sombra con oficio.** Offset y blur suaves. Nunca `box-shadow: 4px 4px 0` ni glow de color sin desplazamiento.

## Shapes

Radios suaves, no píldoras salvo badges. Botones e inputs 8px; cards de app 12px; cards de landing y `small-box` 16px; CTA de landing 10px; badges 999px.

Bordes 1px `{colors.line}` en cards de plataforma, inputs y tablas. Las cards del POS van sin borde, solo sombra. Focus visible: outline 2px índigo, offset 2px; inputs además `box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.16)`.

## Components

Familiar y denso: el control primario se ve cobrable a un metro.

### Buttons
- **Shape:** esquinas suaves (8px), peso 600, no wrap.
- **Primary:** fondo `{colors.primary}`, texto blanco, padding `0.55rem 1.25rem`. Un `<a class="btn-primary">` también lleva texto blanco (el color de link no pisa el botón).
- **Hover / Focus:** fondo `{colors.primary-hover}`, sin translate. Focus-visible: outline índigo 2px.
- **Success:** `{colors.success}` (POS) / verde más oscuro en plataforma para contraste de badge (`#047857`). Texto blanco.
- **Outline secondary:** tinta sobre transparente; “Volver”.
- **Landing CTA:** `{colors.landing}`, radio 10px, padding `12px 20px`.

### Chips
- **Style:** badge píldora, padding `0.15rem 0.55rem`, peso 600, ~0.78rem. Fondos tintados: ok `#ecfdf5` / warn `#fffbeb` / bad `#fef2f2` / info `#eff6ff` / neutral `{colors.surface-2}`.
- **State:** el color del texto sale del mismo hue (no gris sobre color). Pendiente = info; activo = ok; gracia = warn; pausado = bad.

### Cards / Containers
- **Corner Style:** 12px en app, 16px en landing.
- **Background:** `{colors.surface}`.
- **Shadow Strategy:** card ambient (ver Elevation). Landing: borde 1px sin sombra fuerte.
- **Border:** plataforma 1px `{colors.line}`; POS sin borde.
- **Internal Padding:** header ~1.25rem; body ~1.35rem en POS.

### Inputs / Fields
- **Style:** radio 8px, borde `{colors.line}`, min-height 2.75rem, padding `0.55rem–0.65rem 0.85rem–1rem`, fondo superficie.
- **Focus:** borde índigo + halo `rgba(79, 70, 229, 0.15–0.16)`.
- **Error:** borde `{colors.danger}` / `#b91c1c` en plataforma; alert tintado, no barra lateral de color.
- **Labels:** `for`/`id` obligatorios. Placeholder en `{colors.muted}`.

### Navigation
- Barra oscura `{colors.ink}`, links al 78% blanco, hover al 100% con fondo blanco 10%. Activo: píldora índigo (8px). Brand 700, blanco. Toggler en `< md`. Skip-link: ítem índigo que baja a `top: 0.75rem` al foco.
- POS: misma barra oscura + logo configurable. No rearmar ítems ni el orden del menú.
- Plataforma, landing y errores centrales: favicon en `public/brand/` (A blanca sobre índigo). El POS sigue usando `app_favicon` del comercio.

### Stats
- Plataforma: card blanca, número 2rem 700 en tinta o semántico, label muted. 4 columnas → 2 → 1.
- POS: `small-box` con radio 16px y hover lift. No copiar el lift a la plataforma.

## Do's and Don'ts

### Do:
- **Do** usar Outfit 400/500/600/700 y `{colors.primary}` para la acción que cobra o guarda.
- **Do** formatear dinero con `money()` (`Gs.` + miles con punto).
- **Do** conservar la IA de menús del POS; se puede pulir densidad, tipo y color.
- **Do** estados vacíos, error y éxito en cada lista y formulario de plataforma.
- **Do** `tabular-nums` en montos y contadores.

### Don't:
- **Don't** pintar el POS o `/plataforma` con el teal de landing.
- **Don't** poner un kicker/eyebrow encima del h1.
- **Don't** dejar un `a.btn-primary` con color de link: el texto del primario es blanco.
- **Don't** introducir signup público, checkout Stripe/Mercado Pago, ni una segunda piel “admin”.
- **Don't** usar gradient text, glass de adorno, ni emoji como ícono (Font Awesome en POS; texto en plataforma).
