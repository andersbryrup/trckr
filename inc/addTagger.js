$.fn.addTagger = function(mode){
  console.log('test');
  if($('#tagger').length === 0){
    $('body').append('<div id="tagger" style="position: absolute; display:none;"></div>');
    $.get('ajax.php?q=getClients', function(data){
      $.each(data, function(){
        $('#tagger').append('<div class="tagger-client">' + this.name + '</div>');
      });
    });
  }

  if(mode === 'clientOnly') {
    this.on("keyup", function(event){
      $('#tagger').show();

      var position = $(this).offset();
      $('#tagger').css("left", position.left + 5);
      $('#tagger').css("top", position.top + 35);
      $('#tagger').css("width", $(this).width());

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
          }
        });
      }

      if ( chr === '@' ) {
        var position = $(this).offset();
        $('#tagger').css("left", position.left + 5);
        $('#tagger').css("top", position.top + 35);
        $('#tagger').css("width", $(this).width());
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
