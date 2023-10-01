<ul>
@foreach($childs as $child)
	<li id="node_{{ $child->id }}" class="tree-node" data-node-id="{{ $child->id }}" data-node-text="{{ $child->name }}" data-node-level="{{ $child->level }}">
    <span class="node-content">{{ $child->name }}</span>
    <input type="text" class="edit-input" value="{{ $category->name }}" style="display: none;" />
	@if(count($child->childs))
            @include('essentials::settings.partials.departments.treeItem',['childs' => $child->childs])
        @endif
	</li>
@endforeach
</ul>