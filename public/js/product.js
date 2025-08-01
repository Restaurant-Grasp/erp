const quantityModal = document.getElementById('quantityModal');
quantityModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const productId = button.getAttribute('data-product-id');
    const productName = button.getAttribute('data-product-name');

    document.getElementById('modal_product_id').value = productId;
    document.getElementById('modal_product_name').textContent = productName;
});
