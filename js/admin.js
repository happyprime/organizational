( function( $, window ) {
	/**
	 * Remove a previously associated object and update the list of objects
	 * that are now associated with the primary post object.
	 *
	 * @param event
	 */
	function remove_object( event ) {
		var remove_id = $( this ).parent().get( 0 ).id;
		var remove_element = $( "#" + remove_id );
		var remove_name = remove_element.data( "name" );
		var objects_assign_ids = $( "#" + event.data + "-assign-ids" ).val().split( "," );

		var new_objects_assign_ids = [];

		for ( var k in objects_assign_ids ) {
			if ( remove_id !== objects_assign_ids[ k ] ) {
				new_objects_assign_ids.push( objects_assign_ids[ k ] );
			}
		}

		objects_assign_ids = new_objects_assign_ids.join( "," );

		$( "#" + event.data + "-assign-ids" ).val( objects_assign_ids );

		// Update the autocomplete source data with the removed item.
		window.organizational[ event.data ].push( { "value": remove_id, "label": remove_name } );

		// Remove the actual item from the associated list.
		remove_element.remove();
	}

	/**
	 * Hook into jQuery UI's autocomplete for providing a list of possible objects to
	 * be associated with this object.
	 *
	 * @param object_type
	 */
	function autocomplete_object( object_type ) {
		var assign_ids = $( "#" + object_type + "-assign-ids" );
		var assign = $( "#" + object_type + "-assign" );

		assign.autocomplete( {
			appendTo: "#" + object_type + "-results",
			minLength: 0,
			source: window.organizational[ object_type ],
			focus: function( event, ui ) {
				assign.val( ui.item.label );
				return false;
			},
			select: function( event, ui ) {

				// Once an option is selected, clear the input box.
				assign.val( "" );

				// Check to see if this object's ID is already in the list of added objects before adding it.
				if ( 0 >= $( "#" + ui.item.value ).length ) {
					$( "#" + object_type + "-results" ).append( "<div class=\"added-" + object_type + " added-object\" id=\"" + ui.item.value + "\" data-name=\"" + ui.item.label + "\">" + ui.item.label + "<span class=\"organizational-object-close dashicons-no-alt\"></span></div>" );

					var current_ids = assign_ids.val();
					if ( "" === current_ids ) {
						current_ids = ui.item.value;
					} else {
						current_ids += "," + ui.item.value;
					}
					assign_ids.val( current_ids );

					delete window.organizational[ object_type ][ 0 ][ ui.item.value ];
				}

				return false;
			}
		} );
	}

	window.organizational_autocomplete_object = autocomplete_object;
	window.organizational_remove_object = remove_object;

	$( document ).ready( function() {
		if ( 0 !== $( "#organizational_assign_people" ).length ) {
			autocomplete_object( "people" );
			$( "#people-results" ).on( "click", ".organizational-object-close", "people", remove_object );
		}

		if ( 0 !== $( "#organizational_assign_projects" ).length ) {
			autocomplete_object( "projects" );
			$( "#projects-results" ).on( "click", ".organizational-object-close", "projects", remove_object );
		}

		if ( 0 !== $( "#organizational_assign_entities" ).length ) {
			autocomplete_object( "entities" );
			$( "#entities-results" ).on( "click", ".organizational-object-close", "entities", remove_object );
		}

		if ( 0 !== $( "#organizational_assign_publications" ).length ) {
			autocomplete_object( "publications" );
			$( "#publications-results" ).on( "click", ".organizational-object-close", "publications", remove_object );
		}
	} );
}( jQuery, window ) );
