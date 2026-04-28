# 🤝 Guía de Contribución

Gracias por contribuir a este proyecto 🚀
Para mantener el orden y la calidad del código, sigue estas reglas:

---

## 🌳 Flujo de ramas

* `main` → código estable (producción)
* `dev` → integración de cambios

### 🔹 Reglas:

* **NUNCA trabajar directamente en `main`**
* **Todas las ramas nuevas deben salir de `dev`**

```bash
git checkout dev
git checkout -b feature/nombre-feature
```

---

## 🧩 Tipos de ramas

Usa nombres claros:

* `feature/...` → nuevas funcionalidades
* `fix/...` → corrección de errores
* `docs/...` → documentación

Ejemplo:

```bash
feature/login
fix/error-calculo
```

---

## 💾 Commits

Usar el estándar de Conventional Commits:

```bash
feat: agregar login
fix: corregir validación
docs: actualizar README
```

---

## 🔁 Pull Requests

* Los PR deben ir **de feature → dev**
* No hacer PR directamente a `main`
* Agregar descripción clara del cambio
* Explicar qué problema resuelve

---

## ✅ Buenas prácticas

* Hacer commits pequeños y claros
* Probar el código antes de hacer PR
* Mantener el código limpio y documentado

---

## 🚫 No permitido

* Push directo a `main`
* Commits sin descripción clara
* Código sin probar

---

Gracias por contribuir 💪
