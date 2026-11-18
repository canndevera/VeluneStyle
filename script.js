async function addToCart(id){
	const qtyEl = document.getElementById('qty-'+id);
	const qty = qtyEl ? parseInt(qtyEl.value)||1 : 1;
	const form = new FormData();
	form.append('id', id);
	form.append('qty', qty);
	const res = await fetch('add_to_cart.php', {method:'POST', body: form});
	const data = await res.json();
	if (data.ok) {
		const badge = document.getElementById('cart-badge');
		if (badge) badge.textContent = data.count ?? data.cartCount ?? 0;
		const totalEl = document.getElementById('cart-total');
		if (totalEl && typeof data.total !== 'undefined') {
			totalEl.textContent = data.total.toFixed(2);
		}
		alert('Agregado al carrito');
	} else {
		alert('Error al agregar');
	}
}

async function fetchCart(){
	const res = await fetch('get_cart.php');
	return await res.json();
}

async function updateCart(id, qty){
	const payload = {id: id, qty: qty};
	const res = await fetch('update_cart.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
	return await res.json();
}

window.addToCart = addToCart;

// actualizar badge y total al cargar
document.addEventListener('DOMContentLoaded', async ()=>{
	try{
		const c = await fetchCart();
		const badge = document.getElementById('cart-badge');
		if (badge) badge.textContent = c.count || 0;
		const totalEl = document.getElementById('cart-total');
		if (totalEl && typeof c.total !== 'undefined') {
			totalEl.textContent = c.total.toFixed(2);
		}
	}catch(e){}
});

