<!-- checkout.php -->
<button id="checkout-clip" class="btn btn-primary">Confirmar pago</button>

<script>
document.getElementById('checkout-clip').addEventListener('click', async () => {
  const total = <?= $total ?>;  // calcula en PHP el total a pagar
  const res   = await fetch('<?= URL_SITIO ?>modulos/carrito/crear-clip-session.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ total })
  });
  const json = await res.json();
  if (json.paymentUrl) {
    // redirige al Checkout de Clip
    window.location = json.paymentUrl;
  } else {
    alert('Error al iniciar pago: ' + (json.error||''));
  }
});
</script>
