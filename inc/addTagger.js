$.fn.addTagger = function(mode){
  if($('#tagger').length === 0){
    $('body').append('<ul id="tagger" class="dropdown-menu" style="position: absolute; display:none;"></ul>');
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
    if(event.keyCode === 9){
      var val = _self.val();
      var input_client = val.split('@');

      var text = input_client[0] + '@' + $('.tagger-client:visible').text();

      _self.val(text);

      _self.focus();

      _self.trigger('tagger:select', text);
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
