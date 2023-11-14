@foreach($all_responsible_users as $id => $name)
    <option value="{{ $id }}">{{ $name }}</option>
@endforeach