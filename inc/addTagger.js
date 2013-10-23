$.fn.addTagger = function(mode){
  if($('#tagger').length === 0){
    $('body').append('<ul id="tagger" class="dropdown-menu" style="position: absolute; display:none; z-index: 3000;"></ul>');
    $.get('ajax.php?q=getClients', function(data){
      $.each(data, function(){
        $('#tagger').append('<li class="tagger-client"><a href="#">' + this.name + '</a></li>');
      });
    });
  }

  if(mode === 'clientOnly') {
    this.on("keyup", function(event){
      $('#tagger').show();

      var position = $(this).offset();
      $('#tagger').css("left", position.left);
      $('#tagger').css("top", position.top + 27);

      var val = $(this).val();

      $('.tagger-client').show();

      if(val){
        $('.tagger-client').each(function(){
          if($(this).text().toLowerCase().indexOf(val.toLowerCase()) === -1){
            $(this).hide();
          }
        });
      }
    });

  }
  else {
    this.on("keyup", function(event){
      var val = $(this).val();
      var chr = val.slice(-1);
      var input_client = val.split('@');

      $('.tagger-client').show();

      if(input_client[1]){
        $('.tagger-client').each(function(){
          if($(this).text().toLowerCase().indexOf(input_client[1].toLowerCase()) === -1){
            $(this).hide();
            if($('.tagger-client:visible').length === 1){
              $('.tagger-client:visible').addClass('active');
            }
          }
        });
      }

      if ( chr === '@' ) {
        var position = $(this).offset();
        $('#tagger').css("left", position.left);
        $('#tagger').css("top", position.top + 27);
      }

      if ( val.indexOf('@') === -1){
        $('#tagger').hide();
      }
      else {
        $('#tagger').show();
      }
    });
  }

  var _self = this;

  this.on('keydown', function(event){
    if(event.keyCode === 40 || event.keyCode === 38){
      event.preventDefault();

      if(!($('.tagger-client.active').is(':visible')) || $('.tagger-client.active').length === 0){
        $('.tagger-client').removeClass('active');
        $('.tagger-client').nextAll(':visible:first').addClass('active');
      }
      else {
        $('.tagger-client').removeClass('prev-active');
        $('.tagger-client.active').addClass('prev-active');
        $('.tagger-client').removeClass('active');

        // Down
        if(event.keyCode === 40){
          $('.tagger-client.prev-active').nextAll(':visible:first').addClass('active');
        }
        // Up
        else {
          $('.tagger-client.prev-active').prevAll(':visible:first').addClass('active');
        }
      }
    }

    var selectClient = function(){
      var val = _self.val();

      var input_client = val.split('@');
      
      if(input_client.length === 2){
        var text = input_client[0] + '@' + $('.tagger-client.active').text();
      }
      else {
        var text = $('.tagger-client.active').text();
      }

      _self.val(text);

      _self.focus();

      _self.trigger('tagger:select', text);
    }

    if(event.keyCode === 13 && $('#tagger').is(":visible")){
      event.preventDefault();
      selectClient();
    }

    if(event.keyCode === 9){
      selectClient();
    }
  });

  $('body').on('click', '.tagger-client', function(event){
    
    if(mode === 'clientOnly') {
      var text = $(this).text();
      _self.val(text);

      _self.focus();
    }
    else {
      var val = _self.val();
      var input_client = val.split('@');

      var text = input_client[0] + '@' + $(this).text();

      _self.val(text);

      _self.focus();

    }
    _self.trigger('tagger:select', text);
  });

  this.on('blur', function(event){
    $('#tagger').fadeOut(400);
  });
};
