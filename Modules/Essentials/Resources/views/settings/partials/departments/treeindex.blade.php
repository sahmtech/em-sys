@extends('layouts.app')


@section('content')
<!-- Content Header (Page header) -->
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->

<section class="content-header">
    <h1>
        <span></span>
    </h1>
</section>
<style>
   .context-menu {
    display: none;
    position: absolute;
    z-index: 1000;
    background-color: #fff;
    border: 1px solid #ccc;
    padding: 5px 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.context-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.context-menu li {
    padding: 10px;
    cursor: pointer;
}

.context-menu li:hover {
    background-color: #f0f0f0;
}

</style>


<!-- Main content -->
<section class="content">

<div class="panel panel-primary">
			<div class="panel-heading" style="font-size:20px">الهيكل التنظيمي</div>
	  		<div class="panel-body">
	  			<div class="row">
	  				<div class="col-md-9">
	  			    @if(count($departments) > 0)
                      <ul id="tree1">
                                @foreach($departments as $category)
                                    <li id="node_{{ $category->id }}" class="tree-node" data-node-id="{{ $category->id }}" data-node-text="{{ $category->name }}" data-node-level="{{ $category->level }}">
                                        <span class="node-content">{{ $category->name }}</span>
                                        <input type="text" class="edit-input" value="{{ $category->name }}" style="display: none;" />
                                        @if(count($category->childs))
                                            @include('essentials::settings.partials.departments.treeItem', ['childs' => $category->childs])
                                        @endif
                                        <div class="context-menu-trigger"></div>
                                    </li>
                                @endforeach
                            </ul>
                            @else
                            <p>No data available.</p>
                                <button id="addNodeButton" class="btn btn-primary">Add Node</button>
                             @endif

                           
                             <input type="text" id="edit-node-text" style="display: none;">
                              <button id="edit-node-button" style="display: none;">edit</button>
                     </div>
	  				</div>
	  				
	  			</div>

	  			
	  		</div>
        </div>
        <div class="context-menu-trigger"></div>
        <div id="context-menu" class="context-menu" style="display: none;">
    <ul>
        <li id="edit">Edit</li>
        <li id="delete">Delete</li>
        <li id="add">Add</li>
    </ul>
   
</div>


</div>
</section>
<!-- /.content -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script type="text/javascript">
    $.fn.extend({
        treed: function (o) {
            var openedClass = 'fas fa-minus';
            var closedClass = 'fas fa-plus';
            
            if (typeof o != 'undefined') {
                if (typeof o.openedClass != 'undefined') {
                    openedClass = o.openedClass;
                }
                if (typeof o.closedClass != 'undefined') {
                    closedClass = o.closedClass;
                }
            };
           
            /* initialize each of the top levels */
            var tree = $(this);
            tree.addClass("tree2");
            tree.find('li').has("ul").each(function () {
                var branch = $(this);
                var icon = $("<i class='indicator " + closedClass + "'></i>"); // Create the icon element
                branch.prepend(icon);
                branch.addClass('branch');

                // Check if the branch should be initially opened
                if (branch.data('initially-open') || branch.hasClass('initially-open')) {
                    icon.removeClass(closedClass).addClass(openedClass);
                    branch.children().children().show();
               
                }
                // Toggle open/close when clicking on the icon
                icon.on('click', function() {
                    icon.toggleClass(openedClass).toggleClass(closedClass);
                    branch.children().children().toggle();
                });

              
            });
        }
    });

    /* Initialization of treeviews */
    $('#tree1').treed();
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Function to show the context menu
        function showContextMenu(event, nodeId) {
            event.preventDefault();
            var contextMenu = $('#context-menu');
            contextMenu.attr('data-node-id', nodeId);  // Store the node ID in the context menu
         
            contextMenu.css({
                display: 'block',
                left: event.pageX,
                top: event.pageY
            });
        }
    

// Event listener for changes in the input field
     

        // Attach right-click event to context menu trigger
        $('.tree-node').on('contextmenu', function(event) {
                var nodeId = $(this).data('node-id');
        
     
        console.log('Node ID:', nodeId); 
        // Log the node ID
        showContextMenu(event, nodeId);
        return false;
});
$('.tree-node-child').on('contextmenu', function(event) {
                var nodeId = $(this).data('node-id');
        
     
        console.log('Node ID:', nodeId); 
        // Log the node ID
        showContextMenu(event, nodeId);
        return false;
});

        // Close the context menu when clicking outside
        $(document).on('click', function() {
            $('#context-menu').css('display', 'none');
        });

       // Attach click event to Edit context menu item
       $('#edit').on('click', function() {
    var nodeId = $('#context-menu').attr('data-node-id');
    var inputField = $('#edit-node-text');
    var editButton = $('#edit-node-button');
    var nodeContent;

if ($(`#node_${nodeId}`).hasClass('tree-node')) {
    // This is a parent node
    nodeContent = $(`#node_${nodeId} .node-content`).text();
} else if ($(`#node_${nodeId}`).hasClass('tree-node-child')) {
    // This is a child node
    nodeContent = $(`#node_${nodeId} .node-content-child`).text();
}
   console.log(nodeContent);
    inputField.val(nodeContent);  // Set the node text in the input field

    // Show the input field and edit button for editing
    inputField.show();
    editButton.show();
    inputField.focus();

    // Bind a click event to the edit button to save the edited text
    editButton.on('click', function() {
        var newText = inputField.val();
        saveEditedNode(nodeId, newText);
    });
// Bind an event to the "Escape" key to cancel the edit
$(document).keyup(function(e) {
        if (e.key === "Escape") {
            cancelEdit(nodeId, nodeContent);
        }
    });
    // Hide the context menu
    $('#context-menu').css('display', 'none');
});
// Function to cancel the edit
function cancelEdit(nodeId, originalText) {
    var inputField = $('#edit-node-text');
    var editButton = $('#edit-node-button');

    inputField.val(originalText);  // Reset the input field to the original text

    // Hide the input field and edit button
    inputField.hide();
    editButton.hide();

    // Show the node content again
    if ($(`#node_${nodeId}`).hasClass('tree-node')) {
        $(`#node_${nodeId} .node-content`).show();
    } else if ($(`#node_${nodeId}`).hasClass('tree-node-child')) {
        $(`#node_${nodeId} .child`).show();
    }
}


$('#delete').on('click', function() {
            var nodeId = $('#context-menu').attr('data-node-id');
           
            deleteNode(nodeId);
        });

        // AJAX request to edit node
   // AJAX request to edit node
   function saveEditedNode(nodeId, newText) {
    var postData = {
        node_id: nodeId,
        new_text: newText
    };

    $.ajax({
        url: '/hrm/treeview/update/' + nodeId , // Replace with your actual URL and route
        type: 'POST',
        data: postData,
        success: function(response) {
            console.log('Edit action successful for node with ID:', nodeId);
            console.log('Response:', response);

            $(`#node_${nodeId} .node-content`).text(newText);

            // Hide the input field and edit button
            $('#edit-node-text').hide();
            $('#edit-node-button').hide();

            // Show the node content again
            $(`#node_${nodeId} .node-content`).show();

            // Reload the page to reflect the updates
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Error editing node:', error);
        }
    });
}


        // AJAX request to delete node
        function deleteNode(nodeId) {
           
        // Send AJAX request to your controller to delete the node with the given nodeId
        $.ajax({
            url: '/hrm/treeview/delete/'+ nodeId,  // Replace with your actual URL
            type: 'POST',
          
            success: function(response) {
                // Handle success response
                console.log('Delete action successful for node with ID:', nodeId);
                console.log('Response:', response);

                // Reload the page to reflect the updates
                location.reload();
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error('Error deleting node:', error);
            }
        });
    }
// Handle click event for the "Add" context menu item

// Handle click event for the "Add" context menu item
// Handle click event for the "Add" context menu item
$('#add').on('click', function() {
    var nodeId = $('#context-menu').attr('data-node-id');
    var nodeLevel = $('#node_' + nodeId).attr('data-node-level');
    var node = $(`#node_${nodeId}`);
    
    // Show an input field for the user to enter the new node text
    openAddNodeInput(node, nodeId, nodeLevel);
});


// Function to open an input field for adding a new node
function openAddNodeInput(node, parentNodeId ,nodeLevel) {
    var inputField = $('<input type="text" class="add-input" placeholder="" />');
    var addButton = $('<button class="add-button">Add</button>');

    addButton.on('click', function() {
        var newText = inputField.val().trim();
        if (newText !== '') {
            addNode(parentNodeId, newText ,nodeLevel);
        }
        

    });

    node.append(inputField);
    node.append(addButton);

    // Hide the context menu
    $('#context-menu').css('display', 'none');
}

// AJAX request to add a new node
function addNode(parentNodeId, newNodeText ,nodeLevel) {
    var postData = {
        parent_id: parentNodeId,
        new_text: newNodeText,
        level:nodeLevel
    };
   console.log(postData);
    $.ajax({
        url: '/hrm/treeview/add',  // Replace with your actual URL
        type: 'POST',
        data: postData,
        success: function(response) {
            // Handle success response
            console.log('Add action successful for parent node with ID:', parentNodeId);
            console.log('Response:', response);

            // Reload the page to reflect the updates
            location.reload();
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error('Error adding node:', error);
        }
        
    });
}




    });
</script>




@endsection
