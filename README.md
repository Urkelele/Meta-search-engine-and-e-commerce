## 1️⃣ Qué estás construyendo REALMENTE

No estás rehaciendo tu tienda.
Estás construyendo **Amazon encima de tu tienda**.

### Hay 2 niveles:

### 🔹 Nivel 1 — Individual Assignments (IA)

* Tu **TTRPG Shop** (ya hecho)
* El IA de tu compañero (otro dominio)
* Siguen funcionando **igual que antes**
* Con usuarios propios, compras propias, etc.

👉 **NO se tocan ni se rompen**

---

### 🔹 Nivel 2 — Meta-Search Engine (MSE)

Un **nuevo proyecto**, con:

* Su **propia base de datos**
* Sus **propios usuarios**
* Su **propia interfaz**
* Comunicación con los IAs **por HTTP + JSON**

👉 El MSE **NO accede a las bases de datos de los IA**

Esto es MUY importante para la nota.

---

## 2️⃣ Cómo encaja tu TTRPG en el MSE

Tu TTRPG pasa a tener **dos roles**:

### 🟢 Rol A — Tienda normal

* Sigue funcionando como IA
* Usuarios normales pueden:

  * registrarse
  * comprar
  * navegar
* Esto ya lo tienes ✔️

---

### 🟡 Rol B — Proveedor de datos (API)

Tu TTRPG **expone endpoints JSON**, por ejemplo:

```
/api/search.php
/api/item.php
/api/reserve.php
/api/buy.php
/api/orders.php
```

Estos scripts:

* ❌ NO devuelven HTML
* ✅ Devuelven **JSON**
* Son llamados **solo por el MSE**

👉 Estos scripts son **copias adaptadas** de los que ya tienes.

Esto cumple exactamente el requisito **3. Individual Assignment Modifications**.

---

## 3️⃣ El proceso CORRECTO (como el individual)

Vamos a seguir este orden, que es **el mismo enfoque que usaste antes**:

---

## 🧱 FASE 1 — Diseño (OBLIGATORIA)

Antes de escribir código, hay que tener claro:

### 1️⃣ Qué guarda cada base de datos

#### 📦 IA (TTRPG)

* users
* items
* orders
* order_items
* etc.

👉 **No cambia**

#### 🧠 MSE

Solo guarda:

* usuarios del MSE
* pedidos del MSE (referencias externas)

Ejemplo:

```text
mse_users
mse_orders
mse_order_items
```

Donde `mse_order_items` tendrá:

* ia_name (ttrpg, sandwich, etc)
* ia_item_id
* price_at_purchase
* quantity

---

### 2️⃣ Flujo de compra REALISTA (importantísimo)

Esto es lo que más miran:

#### 🛒 Añadir al carrito

1. Usuario MSE añade producto
2. MSE llama a:

   ```
   IA /api/reserve.php
   ```
3. IA:

   * comprueba stock
   * reduce stock
   * responde OK / ERROR

👉 **Aquí hay concurrencia real**

---

#### 💳 Comprar

1. Usuario confirma pago
2. MSE:

   * crea pedido en su DB
   * llama a:

     ```
     IA /api/buy.php
     ```
3. IA:

   * confirma pedido
   * guarda order + order_items

---

#### ❌ Cancelar / timeout

* Si el usuario abandona:

  * MSE llama a `/api/release.php`
  * IA devuelve stock

Esto es **nivel sobresaliente**.

---

## 🧠 FASE 2 — Extender tu TTRPG (IA)

Aquí vamos a trabajar **solo en tu proyecto actual**, pero sin romper nada.

### Objetivo:

👉 Que el MSE pueda hablar con tu tienda

### Qué haremos:

1. Crear carpeta:

   ```
   /api/
   ```
2. Copiar lógica existente y:

   * quitar HTML
   * devolver JSON
3. Crear **usuario técnico del MSE**:

   * no humano
   * con token o password
   * solo para API

Ejemplo:

```php
if (!is_mse_authenticated()) {
    http_response_code(403);
    exit;
}
```

---

## 🌐 FASE 3 — Crear el MSE

Aquí sí empieza el proyecto nuevo.

### Stack:

* PHP (backend)
* MySQL
* JS (AJAX, fetch)
* HTML mínimo

### Estructura típica:

```
/mse
 ├─ api/
 ├─ public/
 ├─ js/
 ├─ includes/
 └─ index.php
```

---

### Qué hace el MSE:

* Unifica búsquedas
* Llama a múltiples IAs
* Normaliza los datos
* Renderiza resultados
* Gestiona carrito
* Gestiona pedidos

Todo **sin recargar la página**.