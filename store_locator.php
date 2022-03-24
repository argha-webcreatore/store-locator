<!-- <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM&v=3&libraries=places"
    defer>
</script> -->

<style type="text/css">
    :root{
      --banner-bg: url(<?php echo $img_path = base_url('assets/img/banners/StoreImage.png');?>);
    }
    
</style>
<section class="locateStore-banner">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                <h5 class="title">Store Locator</h5>
            </div>
        </div>
    </div>
</section>

<section class="bg-light py-3">
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav class="" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#"><svg class="bi" width="18" height="18"><use xlink:href="#home"></use></svg></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Store Locator</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
</section>
<section class="locateMap-store py-5">
    <div class="container-fluid">
        <h2 class="title-underline mb-5">Find a store near you</h2>
        <div class="row">
            <div class="col-lg-5">
                <div class="left-panel">
                    <div class="store-locator-loading d-none">
                        <div class="spinner-border text-dark"></div>
                        <span class="loading-text mt-3">Searching nearest stores...</span>
                    </div>
                    <div class="row">
                        <div class="d-flex">
                            <div class="w-100 inputSearch_container">
                                <div class="search-pan">
                                    <input type="text" class="form-control pr-0" id="inputSearch" placeholder="Enter a town/city, country/region or postcode" autocomplete="off">
                                    <span>
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <div class="search-list-container d-none">
                                    <div class="search-list-container-child">
                                        <ul class="prediction-list-container m-0 p-0">
                                        </ul>
                                        <ul class="use-current-location m-0 p-0">
                                            <li class=" d-flex justify-content-between align-items-center list_item">
                                               <span>
                                                    Use Current Location
                                                </span>
                                                <i class="material-icons">
                                                    near_me
                                                </i> 
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="stores-list-container mt-3">
                        <div class="stores-list">
                            
                        </div>
                        <div class="no-stores-found d-flex flex-column justify-content-center align-items-center d-none">
                            <div class="store-logo">
                                <img class="img-fluid" src="<?php echo base_url('assets/img/store.png');?>">
                            </div>
                            <h5 style="margin-top: 0.5em;">No stores found.</h5>
                        </div>
                        
                        <div class="store-description-container d-none">
                            <div class="store-description-close-icon p-3">
                                <a href="#" class="text-dark">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Back to results</span>
                                </a>
                            </div>

                            <div class="store-description-name-container">
                                <h3></h3>
                                <span></span>
                            </div>
                            <hr class="name-address-divider">
                            <div class="store-description-address-container">
                                <div class="address-left-side">
                                    <div class="direction-icon-container">
                                        <i class="fas fa-directions"></i>
                                    </div>
                                    
                                    <span>
                                        
                                    </span>
                                </div>
                                <div class="address-right-side">
                                    <a href="#" target="_blank">Get directions</a>
                                </div>
                            </div>

                            <div class="store-description-contact-container">
                                <div class="contact-left-side">
                                    <div class="contact-icon-container">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                    <span>
                                        
                                    </span>
                                </div>
                                <div class="contact-right-side">
                                    <a href="#">Call the shop</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="col-lg-7">
                <div id="map"></div>
            </div>

        </div>
    </div>
</section>
<script src="<?php echo base_url()?>assets/js/new/app.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?libraries=places,geometry&key=AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM&callback=navigate_my_location" async defer></script>




<script>
    <?php if(!empty($json)){?>
        let stores = <?php echo $json?>;
    <?php }?>
    var icon1 = "<?php echo base_url('assets/img/blue_location.png')?>";
    var icon2 = "<?php echo base_url('assets/img/black_location.png')?>";
    var base_url = "<?php echo base_url();?>";
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>',
        csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var map;
    var markers = [];
    var infoWindow;
    var marker;
    const labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    let labelIndex = 0;
    var store_list = null;
    var map_zoom_flag = true;
    var latNew;
    var lngNew;
    $('.store-description-close-icon a').click(function(e){
        e.preventDefault();
        this.parentElement.parentElement.classList.add('d-none');
    })


    function createCurrentLocation()
    {
        var google_label = document.querySelector('.pac-container::after');
        var pac_container = document.querySelector('.pac-container');
        var height = pac_container.height;

        google_label.remove();

    }
    $('.use-current-location li').click(function(){
        navigate_my_location();
    })
    function navigate_my_location()
    {
        var latitude = 28.6139;
        var longitude = 77.2090;
        
        initMap(latitude, longitude);
        // var gps = navigator.geolocation;
        // if(gps){
        //     gps.getCurrentPosition(positionSuccessStoresNearMe, function (error) {
        //         console.log("An error occurred... The error code and message are: " + error.code + "/" + error.message);
        //     });
        // }
    }
    
    function positionSuccessStoresNearMe(position) 
    {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        // get_store_listing(latitude, longitude);
        initMap(latitude, longitude);

    }

    function get_store_listing(data)
    {
        var jsondata = JSON.stringify(data);
        var query = `data=${jsondata}&${csrfName}=${csrfHash}`;
        $.ajax({
            url :`${base_url}home/get_store_list`,
            type :"post",
            data : query,
            beforeSend : function()
            {
                $('.store-locator-loading').removeClass('d-none');
            },
            success : function(data)
            {
                $('.store-locator-loading').addClass('d-none');
                if(data.trim() != "not_found")
                {
                    $('.no-stores-found').addClass('d-none');
                    $('.stores-list').html('');
                    $('.stores-list').removeClass('d-none');
                    var object = JSON.parse(data);
                    var element = document.querySelector('.stores-list');
                    element.innerHTML = '';
                    var store_list = '';
                    var location = new Array();
                    for(var i = 0; i < object.length; i++)
                    {
                        // console.log(object[i]);
                        store_list += `<div class="store-container" onclick="update_container_details(this)" id="${object[i].name}#${object[i].latitude}#${object[i].longitude}#${object[i].mobile_number}#${object[i].address}#${object[i].distance_text}">
                                            <div class="store-info-container px-2">
                                                <div class="store-name">
                                                    <span class="title">${object[i].name}</span>
                                                </div>
                                                <div class="store-address">
                                                    <span class="title">${object[i].address}</span>
                                                </div>
                                                <div class="store-phone-number">${object[i].mobile_number}</div>
                                                <div class="store-distance">${object[i].distance_text}</div>
                                            </div>
                                        </div>`;

                        location[i] = new Array();
                        location[i].push(object[i].name);
                        location[i].push(parseFloat(object[i].latitude));
                        location[i].push(parseFloat(object[i].longitude));
                        location[i].push(parseFloat(object[i].mobile_number));
                        location[i].push(object[i].address);
                        location[i].push(object[i].distance_text);
                    }
                
                    clearMarkers();
                    addMarker(location,map);

                    element.innerHTML = store_list;
                }
                else
                {
                    $('.stores-list').html('');
                    $('.stores-list').addClass('d-none');
                    $('.no-stores-found').removeClass('d-none');
                    var bounds = map.getBounds();
                    var center = bounds.getCenter();
                    var neLat = bounds.getNorthEast().lat();
                    var swLat = bounds.getSouthWest().lat();
                    var neLng = bounds.getNorthEast().lng();
                    var swLng = bounds.getSouthWest().lng();
                    var data1 = {
                        lat : latNew,
                        lng : lngNew,
                        neLat : neLat,
                        neLng : neLng,
                        swLat : swLat,
                        swLng : swLng 
                    };
                    var jsondata1 = JSON.stringify(data1);
                    if(jsondata1!=jsondata){
                        get_store_listing(data1);    
                    }else{
                        $('.stores-list').html('');
                        $('.stores-list').addClass('d-none');
                        $('.no-stores-found').removeClass('d-none');
                    }
                    
                }
                
            }
        })
    }

    function update_container_details(store)
    {
        var div = document.querySelector('.store-description-container');
        var id = store.id.split('#');
        $('.store-description-name-container h3').html(id[0]);
        $('.address-left-side span').html(id[4]);
        $('.contact-left-side span').html(id[3]);
        $('.store-description-name-container span').html(id[5]);
        $('.address-right-side a').attr('href',`https://www.google.com/maps/place/${id[1]},${id[2]}`);
        $('.contact-right-side a').attr('href',`tel:${id[3]}`);
        div.classList.remove('d-none');
    }

    function clearMarkers()
    {
        infowindow.close();
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
        markers.length = 0;
    }

    function addMarker(locations, map) {
        var infowindow = new google.maps.InfoWindow();
        var latlngbounds = new google.maps.LatLngBounds();
        var marker, i;
        for (i = 0; i < locations.length; i++)
        {
            var data = locations[i];
            var html = `
              <div class="store-info-window">
                <div class="store-info-name">
                  ${data[0]}
                </div>
              </div>`;
            
            var latlng = new google.maps.LatLng(data[1], data[2]);
            var marker = new google.maps.Marker({
                 position: latlng,
                 map: map,
                 title : `${data[0]}#${data[3]}#${data[4]},#${data[5]},#${data[1]}#${data[2]}`,
                 icon : icon1,
                 draggable: true
            });

            google.maps.event.addListener(marker, 'click', function() {
                var div = document.querySelector('.store-description-container');
                var new_data = this.getTitle().split('#');
                $('.store-description-name-container h3').html(new_data[0]);
                $('.address-left-side span').html(new_data[2]);
                $('.contact-left-side span').html(new_data[1]);
                $('.store-description-name-container span').html(new_data[3]);
                $('.address-right-side a').attr('href',`https://www.google.com/maps/place/${new_data[4]},${new_data[5]}`);
                $('.contact-right-side a').attr('href',`tel:${new_data[1]}`);
                div.classList.remove('d-none');
            });

            google.maps.event.addListener(marker, 'mouseover', function() {
                this.setIcon(icon2);
            });
            google.maps.event.addListener(marker, 'mouseout', function() {
                this.setIcon(icon1);
            });

            markers.push(marker);
        }
    }


    function initMap(lat = null, lng = null) {
        latNew = lat;
        lngNew = lng;
        var myLatlng = new google.maps.LatLng(lat, lng);
        var mapOptions = {
            zoom: 9,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            gestureHandling: 'greedy',
            zoomControl : true
        };
        infowindow = new google.maps.InfoWindow();
        map = new google.maps.Map(document.getElementById('map'), mapOptions);
        marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            // animation: google.maps.Animation.DROP,
            draggable: true
        });



        var input = document.getElementById('inputSearch');

        input.addEventListener('keyup',function(e){
            if($('.search-list-container').hasClass('d-none'))
            {
                $('.search-list-container').removeClass('d-none');
            }
            var prediction_list_container = document.querySelector('.prediction-list-container');
            var val = this.value;
            if (event.keyCode === 13)
            {
                e.preventDefault();
                getLatLongByPlaceName(val).then(function(response){
                    var place_id = response.results[0].place_id;
                    var new_query = `id=${place_id}&${csrfName}=${csrfHash}`;
                    $.ajax({
                        url : "<?php echo base_url('home/get_place_details_by_id');?>",
                        type : "post",
                        data : new_query,
                        success : function(data)
                        {
                            if(data.trim() != 0)
                            {
                                var jsondata = JSON.parse(data);
                                var place = jsondata.result;
                                setPlaceAndStoreListing(place);
                            }
                            document.querySelector('.prediction-list-container').innerHTML = '';
                            
                        }
                    })

                });
            }
            else
            {
                var query = `val=${val}&${csrfName}=${csrfHash}`;
                $.ajax({
                    url : "<?php echo base_url('home/get_search_list');?>",
                    type : "post",
                    data : query,
                    success : function(data)
                    {
                        if(data.trim()!=0)
                        {
                            setListItem(data, csrfName, csrfHash);
                        }
                    }
                })
            }
            
            
        })

        codeLatLng(lat, lng);
        google.maps.event.addListener(marker, 'position_changed', marker_position_changed);
        google.maps.event.addListener(map,'tilesloaded',function(){
            google.maps.event.addListener(map, 'bounds_changed', map_bounds_changed);    
        })
        google.maps.event.addListener(map, 'dragend', map_dagged_event);

    }

    $('select#preLoc').on('change', function() {
        var locName = $(this).find("option:selected").val();
        var locLat = $('option:selected', this).attr('data-lat');
        var locLng = $('option:selected', this).attr('data-lng');
        initMap(locLat, locLng);
    });

    function moveMarker(placeName, latlng, marker, infowindow) {
        marker.setPosition(latlng);
        infowindow.setContent(placeName);
    }

    function codeLatLng(lat, lng) {
        var geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(lat, lng);
        geocoder.geocode({
            'latLng': latlng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    // setAddress(results[1],1);
                    var bounds = map.getBounds();
                    var center = bounds.getCenter();
                    var neLat = bounds.getNorthEast().lat();
                    var swLat = bounds.getSouthWest().lat();
                    var neLng = bounds.getNorthEast().lng();
                    var swLng = bounds.getSouthWest().lng();

                    var data = {
                        lat : lat,
                        lng : lng,
                        neLat : neLat,
                        neLng : neLng,
                        swLat : swLat,
                        swLng : swLng 
                    };
                    get_store_listing(data);
                } else {
                    alert("No results found");
                }
            } else {
                alert("Geocoder failed due to: " + status);
            }
        });
    }

    function setAddress(place, flag)
    {
        if(flag == 1)
        {
            $('#inputSearch').val(place.formatted_address);
        }
        
    }

    function setListItem(data, csrfName, csrfHash)
    {
        var jsondata = JSON.parse(data);
        var prediction_list_container = document.querySelector('.prediction-list-container');
        prediction_list_container.innerHTML = '';
        var prediction = jsondata.predictions;
        for(var i = 0; i < prediction.length; i++)
        {
            var li = document.createElement('li');
            li.className = "d-flex justify-content-between align-items-center list_item";
            li.innerHTML = prediction[i].description;
            li.id = prediction[i].place_id;

            li.onclick = function(){
                var id = this.id;
                var new_query = `id=${id}&${csrfName}=${csrfHash}`;
                $.ajax({
                    url : "<?php echo base_url('home/get_place_details_by_id');?>",
                    type : "post",
                    data : new_query,
                    success : function(data)
                    {
                        var jsondata = JSON.parse(data);
                        var place = jsondata.result;
                        setPlaceAndStoreListing(place);
                    }
                });

                prediction_list_container.innerHTML = '';
            }


            prediction_list_container.append(li);
        }
    }

    function setPlaceAndStoreListing(place)
    {
        infowindow.close();
        marker.setVisible(false);
        if (place.geometry.viewport) {
            var bounds = new google.maps.LatLngBounds();
            var latlng = new google.maps.LatLng(place.geometry.location.lat,place.geometry.location.lng);
            bounds.extend(latlng);
            google.maps.event.clearListeners(map, 'bounds_changed');
            map.fitBounds(bounds);
            google.maps.event.addListener(map, 'bounds_changed', map_bounds_changed);
        } else {
            map.setCenter(place.geometry.location.lng,place.geometry.location.lat);
        }
        google.maps.event.clearListeners(marker, 'position_changed');
        marker.setPosition(place.geometry.location);
        marker.setVisible(true);
        google.maps.event.addListener(marker, 'position_changed', marker_position_changed);
        setAddress(place,1);
        map.setZoom(12);
    }

    function map_dagged_event()
    {
        var bounds = this.getBounds();
        var center = bounds.getCenter();
        var neLat = bounds.getNorthEast().lat();
        var swLat = bounds.getSouthWest().lat();
        var neLng = bounds.getNorthEast().lng();
        var swLng = bounds.getSouthWest().lng();

        var FinLat = this.getCenter().lat().toFixed(3);
        var FinLng = this.getCenter().lng().toFixed(3);
        var latlng = new google.maps.LatLng(FinLat, FinLng);
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'latLng': latlng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    // map.setZoom(10);
                    setAddress(results[1],1);
                    var data
                    var data = {
                        lat : FinLat,
                        lng : FinLng,
                        neLat : neLat,
                        neLng : neLng,
                        swLat : swLat,
                        swLng : swLng 
                    };
                    get_store_listing(data);
                }
            }
        });
    }

    function marker_position_changed()
    {
        google.maps.event.clearListeners(marker, 'position_changed');
        var FinLat = this.getPosition().lat().toFixed(3);
        var FinLng = this.getPosition().lng().toFixed(3);
        latNew = FinLat;
        lngNew = FinLng;
        var latlng = new google.maps.LatLng(this.getPosition().lat(), this.getPosition().lng());
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'latLng': latlng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    map.setZoom(12);
                    setAddress(results[1],1);
                    var bounds = map.getBounds();
                    var center = bounds.getCenter();
                    var neLat = bounds.getNorthEast().lat();
                    var swLat = bounds.getSouthWest().lat();
                    var neLng = bounds.getNorthEast().lng();
                    var swLng = bounds.getSouthWest().lng();
                    var data = {
                        lat : FinLat,
                        lng : FinLng,
                        neLat : neLat,
                        neLng : neLng,
                        swLat : swLat,
                        swLng : swLng 
                    };
                    get_store_listing(data);
                }
            }
        });
    }
    var i = 0;
    function map_bounds_changed()
    {
        google.maps.event.clearListeners(map, 'bounds_changed');
        google.maps.event.addListener(map, 'bounds_changed', map_bounds_changed);
        var bounds = this.getBounds();
        var center = bounds.getCenter();
        var neLat = bounds.getNorthEast().lat();
        var swLat = bounds.getSouthWest().lat();
        var neLng = bounds.getNorthEast().lng();
        var swLng = bounds.getSouthWest().lng();
        var FinLat = this.getCenter().lat().toFixed(3);
        var FinLng = this.getCenter().lng().toFixed(3);
        var latlng = new google.maps.LatLng(FinLat, FinLng);
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'latLng': latlng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    // map.setZoom(10);
                    setAddress(results[1],1);
                    var data
                    var data = {
                        lat : FinLat,
                        lng : FinLng,
                        neLat : neLat,
                        neLng : neLng,
                        swLat : swLat,
                        swLng : swLng 
                    };
                    get_store_listing(data);
                }
            }
        });
    }

    function getLatLongByPlaceName(address)
    {
        var geocoder = new google.maps.Geocoder();
        return geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            return results[0];
          } 
        });
    }

    function calculateMapRadius(bounds, center, ne)
    {
        // r = radius of the earth in statute miles
        var r = 3963.0;  
        // Convert lat or lng from decimal degrees into radians (divide by 57.2958)
        var lat1 = center.lat() / 57.2958; 
        var lon1 = center.lng() / 57.2958;
        var lat2 = ne.lat() / 57.2958;
        var lon2 = ne.lng() / 57.2958;
        // distance = circle radius from center to Northeast corner of bounds
        var dis = r * Math.acos(Math.sin(lat1) * Math.sin(lat2) + 
          Math.cos(lat1) * Math.cos(lat2) * Math.cos(lon2 - lon1));

        return dis;
    }
    
</script>

<script src="<?php echo base_url()?>assets/map/index.js"></script>