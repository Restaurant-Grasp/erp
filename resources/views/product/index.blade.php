@extends('layouts.app')

@section('title', 'Product List')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Product List</h5>
        <a href="{{ route('product.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Product
        </a>
    </div>

    <div class="card-body">
       

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Product Code</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>UOM</th>
                            <th>Cost Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($productList as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->product_code }}</td>
                            <td>{{ $product->category->name ?? '' }}</td>
                            <td>{{ $product->brand->name ?? '' }}</td>
                            <td>{{ $product->model->name ?? '' }}</td>
                            <td>{{ $product->uom->name ?? '' }}</td>
                            <td>{{ $product->cost_price }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#quantityModal"
                                    data-product-id="{{ $product->id }}"
                                    title="Quantity"
                                    data-product-name="{{ $product->name }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <a href="{{ route('product.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>


                                <form method="POST" action="{{ route('product.destroy', $product) }}" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this Product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <p>No Products found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
      

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $productList->firstItem() ?? 0 }} to {{ $productList->lastItem() ?? 0 }} of {{ $productList->total() }} entries
            </div>
            {{ $productList->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="quantityForm" method="POST" action="{{ route('product.addQuantity') }}">
            @csrf
            <input type="hidden" name="product_id" id="modal_product_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityModalLabel">Opening Stock - <span id="modal_product_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price </label>
                        <input type="number" name="price" id="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            <option value="">-- Select Warehouse --</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Quantity</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection