@extends('layouts.app')
@section('content')

<form action="{{ route('poll.store') }}" method="post">
    @csrf
    <div class="form-body">
        <div>
            <label for="title" class="mb-2">Название опроса</label>
            <input class="form-control mb-3" type="text" id="title" name="title" required>
        </div>
        <div>
            <label for="variants[]" class="mb-2">Вариант 1</label>
            <input class="form-control mb-3" type="text" name="variants[]" required>
        </div>
        <div>
            <label for="variants[]" class="mb-2">Вариант 2</label>
            <input class="form-control mb-3" type="text" name="variants[]" required>
        </div>
    </div>
    <button onclick="addVariant()" class="btn btn-primary" type="button">Добавить ещё вариант</button>
    <button class="btn btn-success" type="submit">Сохранить опрос</button>
</form>
<script>
    var variantsCount = 2;
    var formBodySelector = document.querySelector('.form-body');

    function addVariant() {
        variantsCount++;
        formBodySelector.innerHTML += `
        <div data-variant-id="${variantsCount}">
            <label for="variants[]" class="mb-2">Вариант ${variantsCount}</label>
            <div style="display: flex; gap: 10px;">
            <input class="form-control mb-3" type="text" name="variants[]" required>
            <button onclick="removeVariant(${variantsCount})" class="btn btn-danger" style="height: 40px;"  type="button">Удалить</button>
            </div>
        </div>`;
    }

    function removeVariant(varId) {
        var temp = document.querySelector(`[data-variant-id="${varId}"]`).remove();
        if (varId === variantsCount) {
            variantsCount--;
        }
    }
</script>
@endsection