<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <meta charset="UTF-8">
        <title>Drawing Tools   Marker Circle Converted  </title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwaRvIypypSVDBBlxdkFdULRY6mFmN7fA&libraries=drawing&callback=initMap" async defer></script>
        <style type="text/css">
            #map, html, body {
                padding: 0;
                margin: 5px 0px 0px 10px;
                width: 1300px;
                height: 400px;
            }

            #panel {
                width: 200px;
                font-family: Arial, sans-serif;
                font-size: 13px;
                float: right;
                margin: 10px;
            }

            #color-palette {
                clear: both;
            }

            .color-button {
                width: 14px;
                height: 14px;
                font-size: 0;
                margin: 2px;
                float: left;
                cursor: pointer;
            }

            #delete-button {
                margin-top: 5px;
            }
        </style>
        <script type="text/javascript">
            var drawingManager;
            var selectedShape;
            var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
            var selectedColor;
            var colorButtons = {};

            function clearSelection () {
                if (selectedShape) {
                    if (selectedShape.type !== 'marker') {
                        selectedShape.setEditable(false);
                    }
                    
                    selectedShape = null;
                }
            }

            function setSelection (shape) {
                if (shape.type !== 'marker') {
                    clearSelection();
                    shape.setEditable(true);
                    selectColor(shape.get('fillColor') || shape.get('strokeColor'));
                }
                
                selectedShape = shape;
            }

            function deleteSelectedShape () 
			{
				retval = convertFormat(  selectedShape.type ,  selectedShape);
				$("#start_lat_lng").val(retval);
				$("#end_lat_lng").val(''); 
				
				setFormIndata( 'delete' ,  $("#start_lat_lng").val()  ) ;
				$("#start_lat_lng").val('');
				
                if (selectedShape) {
                    selectedShape.setMap(null);
                }
            }

            function selectColor (color) {
                selectedColor = color;
                for (var i = 0; i < colors.length; ++i) {
                    var currColor = colors[i];
                    colorButtons[currColor].style.border = currColor == color ? '2px solid #789' : '2px solid #fff';
                }

                // Retrieves the current options from the drawing manager and replaces the
                // stroke or fill color as appropriate.
                var polylineOptions = drawingManager.get('polylineOptions');
                polylineOptions.strokeColor = color;
                drawingManager.set('polylineOptions', polylineOptions);

                var rectangleOptions = drawingManager.get('rectangleOptions');
                rectangleOptions.fillColor = color;
                drawingManager.set('rectangleOptions', rectangleOptions);

                var circleOptions = drawingManager.get('circleOptions');
                circleOptions.fillColor = color;
                drawingManager.set('circleOptions', circleOptions);

                var polygonOptions = drawingManager.get('polygonOptions');
                polygonOptions.fillColor = color;
                drawingManager.set('polygonOptions', polygonOptions);
            }

            function setSelectedShapeColor (color) 
			{
                if (selectedShape) {
                    if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
                        selectedShape.set('strokeColor', color);
                    } else {
                        selectedShape.set('fillColor', color);
                    }
                }
				
				var end_val = $("#end_lat_lng").val();
				if(end_val!='')
				{
				  $("#start_lat_lng").val(end_val);
				}
					
				retval = convertFormat( selectedShape.type ,  selectedShape);
				$("#end_lat_lng").val(retval); 
				setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;
				clearSelection(); 
            }

            function makeColorButton (color) {
                var button = document.createElement('span');
                button.className = 'color-button';
                button.style.backgroundColor = color;
                google.maps.event.addDomListener(button, 'click', function () {
                    selectColor(color);
                    setSelectedShapeColor(color);
                });

                return button;
            }

            function buildColorPalette () {
                var colorPalette = document.getElementById('color-palette');
                for (var i = 0; i < colors.length; ++i) {
                    var currColor = colors[i];
                    var colorButton = makeColorButton(currColor);
                    colorPalette.appendChild(colorButton);
                    colorButtons[currColor] = colorButton;
                }
                selectColor(colors[0]);
            }
			
			// type =append/replace/delete 
		   function setFormIndata( type ,  start_position , end_position='' )
		   {	   
				g_drawdata = $(".g_drawdata").val();
				if(  type =='append')
				{
					g_drawdata+=start_position+"###";
				}
				else if(  type =='replace')
				{
					g_drawdata = g_drawdata.replace(start_position, end_position);
				}
				else if(  type =='delete')
				{
					g_drawdata = g_drawdata.replace(start_position+"###", "");
				}
				
				$(".g_drawdata").val(g_drawdata);
			}
			
			
		   function convertFormat(type , data) 
		   {
				if( type=='marker' )
				{
					datajson =  {type:'marker',latlng:JSON.stringify(data.position)} ;
				}
				else if( type=='circle' )
				{
					datajson =  {type:'circle','color':data.fillColor,radius:data.getRadius(),latlng:JSON.stringify(data.center)} ; 
				}
				else if( type=='polygon' )
				{
					datajson =  {type:'polygon','color':data.fillColor,latlng:JSON.stringify(data.latLngs['b'][0]['b'])} ;
				}
				else if( type=='rectangle' )
				{
					var bounds = data.getBounds();
					var NE = bounds.getNorthEast();	
					var SW = bounds.getSouthWest();
					var NW = new google.maps.LatLng(NE.lat(), SW.lng()); 
					var SE = new google.maps.LatLng(SW.lat(), NE.lng());
					var areaBounds = {
						 0: NE,
						 1: SE,
						 2: SW,
						 3: NW,
					};
					datajson =  {type:'rectangle','color':data.fillColor,latlng:JSON.stringify(areaBounds),'bound':JSON.stringify(bounds)};
				}
				else if( type=='polyline' )
				{
					datajson =  {type:'polyline','color':data.strokeColor,latlng:JSON.stringify(data.latLngs['b'][0]['b'])};
				}
				
				return JSON.stringify(datajson) ;
		   }
		   
		   function setData( type ,  action , data)
		   {
				selectedShape =  data;
				
				if( action=='click' )
				{
					retval = convertFormat(  type ,  selectedShape);
					$("#start_lat_lng").val(retval);
					$("#end_lat_lng").val('');	
					selectedShape.setEditable(true);
				}
				
				if( action=='dragstart' )
				{
					retval = convertFormat(  type ,  selectedShape);
					$("#start_lat_lng").val(retval);
				}
				
				if( action=='dragend' )
				{
					retval = convertFormat(  type ,  selectedShape);
					$("#end_lat_lng").val(retval);
					setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;
					selectedShape.setEditable(false);
				}
				
				if( action=='radius_changed' )
				{	
					retval = convertFormat( type ,  selectedShape);
					$("#end_lat_lng").val(retval);
					setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;
					selectedShape.setEditable(false);
				}
				
				if( action=='center_changed' )
				{
					if(selectedShape.editable==true)
					{
						retval = convertFormat( type ,  selectedShape);
						$("#end_lat_lng").val(retval);
						setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;
						selectedShape.setEditable(false);
					}
				}
				
				if( action=='insert_at' )
				{
					end_val = $("#end_lat_lng").val(); 
					if(end_val!='')
					{
						$("#start_lat_lng").val(end_val); 
					}
					
					retval = convertFormat( type , selectedShape);
					$("#end_lat_lng").val(retval);
					setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;
					selectedShape.setEditable(false);
				}
				
				if( action=='set_at' )
				{
					if(selectedShape.editable==true)
					{
						end_val = $("#end_lat_lng").val(); 
						if(end_val!='')
						{
							$("#start_lat_lng").val(end_val); 
						}
						
						retval = convertFormat(  type , selectedShape);
						$("#end_lat_lng").val(retval); 
						
						setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val() ) ;	
					}
				}
				
				if( action=='bounds_changed' )
				{					
					if(selectedShape.editable==true)
					{
						end_val = $("#end_lat_lng").val(); 
						if(end_val!='')
						{
							$("#start_lat_lng").val(end_val); 
						}
						
						retval = convertFormat( type , selectedShape);
						$("#end_lat_lng").val(retval); 
						
						setFormIndata( 'replace' ,   $("#start_lat_lng").val() ,  $("#end_lat_lng").val()  ) ;		
					}
				}
		   }
            function initMap () {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {lat: 28.6207557, lng:  77.2219661},
					zoom: 11,
                    //mapTypeId: google.maps.MapTypeId.SATELLITE,
                    disableDefaultUI: true,
                    zoomControl: true 
                });
				
                var polyOptions = {
                    strokeWeight: 0,
                    fillOpacity: 0.45,
                    editable: true,
                    draggable: true,
                };
				
				
                // Creates a drawing manager attached to the map that allows the user to draw
                // markers, lines, and shapes.
                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.HAND,
                    markerOptions: {
                        draggable: true
                    },
                    polylineOptions: {
                        editable: true,
                        draggable: true
                    },
                    rectangleOptions: polyOptions,
                    circleOptions: polyOptions,
                    polygonOptions: polyOptions,
                    map: map
                });
				
				// editable mode 
				<?php 
				// code for drawing cirlce ,rectangle , polyline ,polygon
				$g_drawdata= @$_REQUEST['g_drawdata'];
				
				if( $g_drawdata!='' )
				{
					 $g_drawdata   = explode("###" , rtrim($g_drawdata, "###") );
					 $counter=0;
					 foreach($g_drawdata as $gdata)
					 {
						$counter++;
						$new_data =  json_decode($gdata , true);
						$lat_lng = json_decode(@$new_data['latlng'], true);
						
						if($new_data['type']=='marker')
						{
							  ?>
								var marker<?php echo $counter  ?> = new google.maps.Marker({
								  position: {lat: <?php echo $lat_lng['lat']; ?>, lng: <?php echo $lat_lng['lng']; ?>},
								  map: map,
								  draggable: true
								});
								marker<?php echo $counter  ?>.setMap(map);
								
								google.maps.event.addListener(marker<?php echo $counter  ?>, 'click', function() 
								{
									setData( '<?php echo $new_data['type']; ?>' , 'click'  , marker<?php echo $counter  ?>  );		
								});
								
								google.maps.event.addListener(marker<?php echo $counter  ?>, 'dragstart', function () 
								{
									setData( '<?php echo $new_data['type']; ?>' , 'dragstart'  , marker<?php echo $counter  ?>  );		
								});

								google.maps.event.addListener(marker<?php echo $counter  ?>, 'dragend', function () 
								{
									setData( '<?php echo $new_data['type']; ?>' , 'dragend'  , marker<?php echo $counter  ?>  );		
								});
							  <?php 
						}
						elseif($new_data['type']=='circle')
						{
							  ?>
								new google.maps.Circle({
								strokeOpacity: 0.8,
								strokeWeight: 2,
								fillOpacity: 0.35,
								map: map,
								center: {lat: <?php echo $lat_lng['lat']; ?>, lng: <?php echo $lat_lng['lng']; ?>},
								radius: <?php echo ($new_data['radius']) ?>
								});
							  <?php 
						}
						elseif($new_data['type']=='polygon' || $new_data['type']=='rectangle')
						{
							?>
							// Define the LatLng coordinates for the polygon.
							var polygon_cord = [
							<?php  
							foreach($lat_lng as $pol_point )
							{
								echo '{lat: '.$pol_point['lat'].', lng: '.$pol_point['lng'].'},';
							}
							?>
							];

							// Construct the polygon.
							var polygon_map = new google.maps.Polygon({
							  paths: polygon_cord,
							  strokeOpacity: 0.8,
							  strokeWeight: 3,
							  fillOpacity: 0.35
							});
							polygon_map.setMap(map);
							
					<?php }
						  elseif($new_data['type']=='polyline' )
						  { 
							?>
							// Define the LatLng coordinates for the polygon.
							var polyline_cord = [
							<?php  
							foreach($lat_lng as $pol_point )
							{
								echo '{lat: '.$pol_point['lat'].', lng: '.$pol_point['lng'].'},';
							}
							?>
							];

							// Construct the polygon.
							var polyline_map = new google.maps.Polyline({
								path: polyline_cord,
								geodesic: true,
								strokeOpacity: 1.0,
								strokeWeight: 2
							});
							polyline_map.setMap(map);
				 <?php   }
					 }	
				 }
				?>
				
				// editable mode 
				
				
					/*  marker Drag  and drop   */
					google.maps.event.addListener(drawingManager, 'markercomplete', function (marker) 
					{
						google.maps.event.addListener(marker, 'click', function() 
						{
							setData( 'marker' , 'click'  , marker  );
						});
						google.maps.event.addListener(marker, 'dragstart', function () 
						{
							setData( 'marker' , 'dragstart'  , marker  );
						});

						google.maps.event.addListener(marker, 'dragend', function () 
						{
							setData( 'marker' , 'dragend'  , marker  );
						});				
					});
				
				/*  marker Drag  and drop   */
				
				/*  Circle Drag  and drop   */
					google.maps.event.addListener(drawingManager, 'circlecomplete', function (circle) 
					{
					
						google.maps.event.addListener(circle, 'click', function() 
						{
								setData( 'circle' , 'click'  , circle  );
						});
					
						google.maps.event.addListener(circle, 'dragstart', function () 
						{								 
							setData( 'circle' , 'dragstart'  , circle  );								
						});

						google.maps.event.addListener(circle, 'dragend', function () 
						{
							setData( 'circle' , 'dragend'  , circle  );		
						});
						
						/*  Radius Changed    */
						google.maps.event.addListener(circle, 'radius_changed', function() 
						{
								setData( 'circle' , 'radius_changed'  , circle  );	
						});
						
						/*  Center Changed   */  
						google.maps.event.addListener(circle, 'center_changed', function() 
						{
								setData( 'circle' , 'center_changed'  , circle  );	
						});
						 
					});
					
					/*  Circle Drag  and drop   */
				
					/*  polygon Drag  and drop   */
					
					google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) 
					{
						google.maps.event.addListener(polygon, 'click', function() 
						{
								setData( 'polygon' , 'click'  , polygon  );					
						});
						
						google.maps.event.addListener(polygon, 'dragstart', function () 
						{
							setData( 'polygon' , 'dragstart'  , polygon  );
						});

						google.maps.event.addListener(polygon, 'dragend', function () 
						{
							setData( 'polygon' , 'dragend'  , polygon  );
						});
						
						 //when new line added/changed
						google.maps.event.addListener(polygon.getPath(), 'insert_at', function() 
						{
								setData( 'polygon' , 'insert_at'  , polygon  );		
						});
						
						 // always call when drag 
						google.maps.event.addListener(polygon.getPath(), 'set_at', function() 
						{
							setData( 'polygon' , 'set_at'  , polygon  );	
						});
						
					});
				/*  polygon Drag  and drop   */
				
				
				/*  Rectangle Drag  and drop   */
					
					google.maps.event.addListener(drawingManager, 'rectanglecomplete', function (rectangle) 
					{
						google.maps.event.addListener(rectangle, 'click', function() 
						{	
							setData( 'rectangle' , 'click'  , rectangle  );	

						});
						google.maps.event.addListener(rectangle, 'dragstart', function () 
						{
							setData( 'rectangle' , 'dragstart'  , rectangle  );	
						});

						google.maps.event.addListener(rectangle, 'dragend', function () 
						{ 
							setData( 'rectangle' , 'dragend'  , rectangle  );	
						});

						google.maps.event.addListener(rectangle, 'bounds_changed', function() 
						{
							setData( 'rectangle' , 'bounds_changed'  , rectangle  );		
						});

						
					});
				/*  Rectangle Drag  and drop   */
				
				/*  polyline Drag  and drop   */
					
					google.maps.event.addListener(drawingManager, 'polylinecomplete', function (polyline) 
					{
						google.maps.event.addListener(polyline, 'click', function() 
						{
							setData( 'polyline' , 'click'  , polyline  );		
						});

						google.maps.event.addListener(polyline, 'dragstart', function () 
						{
							setData( 'polyline' , 'dragstart'  , polyline  );		
						});

						google.maps.event.addListener(polyline, 'dragend', function () 
						{
							setData( 'polyline' , 'dragend'  , polyline  );		
						});

						google.maps.event.addListener(polyline.getPath(), 'insert_at', function() 
						{
								setData( 'polyline' , 'insert_at'  , polyline  );		
						});
						
						google.maps.event.addListener(polyline.getPath(), 'set_at', function() 
						{
							setData( 'polyline' , 'set_at'  , polyline  );	
						});
						
						/*  polyline Changed   */
						
					});
				/*  polyline Drag  and drop   */
				
				
                google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) 
				{
				
					retval = convertFormat( e.type ,  e.overlay);
					$("#start_lat_lng").val(retval); 
					$("#end_lat_lng").val('');
					setFormIndata( 'append' ,   $("#start_lat_lng").val()  ) ;
						
                    var newShape = e.overlay;
                    newShape.type = e.type;
                    
                    if (e.type !== google.maps.drawing.OverlayType.MARKER) {
                        // Switch back to non-drawing mode after drawing a shape.
                        drawingManager.setDrawingMode(null);

                        // Add an event listener that selects the newly-drawn shape when the user
                        // mouses down on it.
                      
					   google.maps.event.addListener(newShape, 'click', function (e) {
                            if (e.vertex !== undefined) {
                                if (newShape.type === google.maps.drawing.OverlayType.POLYGON) {
                                    var path = newShape.getPaths().getAt(e.path);
                                    path.removeAt(e.vertex);
                                    if (path.length < 3) {
                                        newShape.setMap(null);
                                    }
                                }
                                if (newShape.type === google.maps.drawing.OverlayType.POLYLINE) {
                                    var path = newShape.getPath();
                                    path.removeAt(e.vertex);
                                    if (path.length < 2) {
                                        newShape.setMap(null);
                                    }
                                }
                            }
                            setSelection(newShape);
                        });
						
                        setSelection(newShape);
                    }
                    else {
                        google.maps.event.addListener(newShape, 'click', function (e) {
                            setSelection(newShape);
                        });
                        setSelection(newShape);
                    }
                });

                // Clear the current selection when the drawing mode is changed, or when the
                // map is clicked.
                google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                google.maps.event.addListener(map, 'click', clearSelection);
                google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);

                buildColorPalette();
				
            }
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </head>
    <body>
        <div id="panel">
            <div id="color-palette"></div>
            <div>
                <button id="delete-button">Delete Selected Shape</button>
            </div>
        </div>
		
		<form method="post" >
		<textarea  id="start_lat_lng" rows="9" cols="30"  placeholder="Start position"></textarea>
		<textarea  id="end_lat_lng" rows="9" cols="30"  placeholder="End position" ></textarea>
		<textarea name="g_drawdata" class="g_drawdata" rows="9" cols="60" placeholder="Drawing Content" ><?php echo @$_REQUEST['g_drawdata']; ?></textarea>
		<input type="Submit" value="Submit" />
		</form>
		<div id="map"></div>
    </body>
</html>
