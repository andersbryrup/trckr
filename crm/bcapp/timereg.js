var timeRegForm = {

  clear : function() {
          $("#hours").val(''); 
          $("#description").val(''); 
          $("#comment").val(''); 
          $("#filter").val('');
          $("#cases").html(timeRegForm.config.casesHtml);
          },

  save : function() {
           var formData = $("#time_form").serialize();
           var caseID = $("#cases option:selected").val();
           var caseName = $("#cases option:selected").html();
           var accountID = timeRegForm.config.casesToAccounts[caseID];
           formData = formData+"&account_id="+accountID+"&caseName="+caseName;
           $.ajax({
             url: 'ajax.php?action=save',
             dataType: 'json',
             data: formData,
             success: function(data){
               if ( !data.error )
               {
                 timeRegForm.clear();
                 timeRegForm.log('succes',data.msg);
                  $("#filter").focus();
               }
               else
               {
                 timeRegForm.log('error',data.msg);
               }
             }
           });
         },

  log : function( type, msg) {
        $("#log").prepend('<div class="'+type+'">'+msg+'</div');
        },

  validatorInit : function() {
                    $("#time_form").validate({
                      rules : {
                          case_id: "required",
                          hours: "required",
                          description: "required",
                          day: "required",
                          year: {
                                required: true,
                                minlength: 4
                              }
                              },
                      messages : {
                                 hours : 'Feltet er påkrævet'
                               },
                      submitHandler: function() {
                                     timeRegForm.save();
                                   }
                    });
                  },

  filterInit : function() {
           $("#filter").keyup(function() {
             var input = $("#filter").val();
             var name = '';

             // smart case detection :)
             var hasUpperCase = false;
             if ( input != input.toLowerCase() )
             {
               hasUpperCase = true;
               $("#smartcase").show();
             }
             else
             {
               $("#smartcase").hide();
             }

             // TODO: should not be called on every keypress
             //timeRegForm.addOptions('#cases',timeRegForm.config.cases);
             $("#cases").html(timeRegForm.config.casesHtml);
             $("#cases option").each(function() {
                if ( !hasUpperCase )
                {
                  name = $(this).text().toLowerCase();
                }
                else
                {
                  name = $(this).text();
                }
                if ( name.indexOf( input ) == -1 )
                {
                  // have to use remove() instead of hide(), because arrow down keypress in the case list will still select the hidden elements
                  $(this).remove();
                }
             });
           });
           },

  refreshCases : function() {
                   $.ajax({
                     url: 'ajax.php?action=jsInit',
                     dataType: 'json',
                     success: function(data){
                       if ( !data.error )
                       {
                         timeRegForm.config.cases = data.cases;
                         timeRegForm.config.casesToAccounts = data.casesToAccounts;
                         timeRegForm.config.casesHtml = timeRegForm.addOptions('#cases',data.cases);
                         $("#cases").focus();
                       }
                       else
                       {
                         timeRegForm.log('error',data.msg);
                       }
                     }
                   });
                 },

  init : function() {
           timeRegForm.config = {
             cases : {},
             casesToAccounts : {},
             casesHtml : ''
           };

           $.ajax({
             url: 'ajax.php?action=jsInit',
             dataType: 'json',
             success: function(data){
               if ( !data.error )
               {
                 timeRegForm.config.cases = data.cases;
                 timeRegForm.config.casesToAccounts = data.casesToAccounts;
                 timeRegForm.config.casesHtml = timeRegForm.addOptions('#cases',data.cases);
                 timeRegForm.validatorInit();
                 timeRegForm.filterInit();
                 $("#filter").focus();
               }
               else
               {
                 timeRegForm.log('error',data.msg);
               }
             }
           });

           $("#refresh_cases").click(function(e) {
             e.preventDefault();
             timeRegForm.refreshCases();
             $("#filter").focus();
           });
  },

  addOptions : function(dest,options) {
               var html = '';
               for(var i in options)
               {
                 html = html +'<option value="'+ options[i].id+'">'+options[i].name+'</option>';
               }
               $(dest).html(html);
               return html;
               }

};

var asteriskToContactForm = {
  init: function () {
    $( "#account" ).autocomplete({
      source: "ajax.php?action=completeAccount",
      minLength: 2,
      select: function( event, ui ) {
        if ( ui.item )
        {
          $("#account_id").val(ui.item.id);
          $(":submit").show();
        }
      }
    }); 

    $(":submit").click(function(e) {
      e.preventDefault();
      var formData = $("form#asterisk-to-contact").serialize();
      $.ajax({
        url: 'ajax.php?action=saveContact',
        type: 'post',
        dataType: 'json',
        data: formData,
        success: function(data) {
          $("#log").append(data.msg+'<br>');
          $('form#asterisk-to-contact input').each(function(){
            $(this).val('');
          });
          $(":submit").hide();
        }
      });
    });
  }
};
