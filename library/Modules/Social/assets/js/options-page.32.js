$(document).ready(function($) {
   var addthis_credential_validation_status = $("#addthis_credential_validation_status");
   var addthis_validation_message = $("#addthis-credential-validation-message");
   var addthis_profile_validation_message = $("#addthis-profile-validation-message");
   //Validate the Addthis credentials
   window.skipValidationInternalError = false;
   function validate_addthis_credentials() {
        $.ajax(
            {"url" : addthis_option_params.wp_ajax_url,
             "type" : "post",
             "data" : {"action" : addthis_option_params.addthis_validate_action,
                      "addthis_profile" : $("#addthis_profile").val(),
                      "addthis_username" : $("#addthis_username").val(),
                      "addthis_password" : $("#addthis_password").val()
                  },
             "dataType" : "json",
             "beforeSend" : function() {
                 $(".addthis-admin-loader").show();
                 addthis_validation_message.html("").next().hide();
                 addthis_profile_validation_message.html("").next().hide();
             },
             "success": function(data) {
                 addthis_validation_message.show();
                 addthis_profile_validation_message.show();

                 if (data.credentialmessage == "error" || (data.profileerror == "false" && data.credentialerror == "false")) {
                     if (data.credentialmessage != "error") {
                         addthis_credential_validation_status.val(1);
                     } else {
                         window.skipValidationInternalError = true;
                     }
                     $("#addthis_settings").submit();
                 } else {
                     addthis_validation_message.html(data.credentialmessage);
                     addthis_profile_validation_message.html(data.profilemessage);
                     if (data.profilemessage != "") {
                         $('html, body').animate({"scrollTop":0}, 'slow');
                     }
                 }

             },
             "complete" :function(data) {
                 $(".addthis-admin-loader").hide();
             },
             "error" : function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
             }
        });
    }
    //Prevent default form submission
    $("#addthis_settings").submit(function(){
        if(window.skipValidationInternalError) {
            return true;
        }
        var isProfileEmpty = $.trim($("#addthis_profile").val()) == "";
        var isUsernameEmpty = $.trim($("#addthis_username").val()) == "";
        var isPasswordEmpty = $.trim($("#addthis_password").val()) == "";
        var isAnyFieldEmpty = isProfileEmpty || isUsernameEmpty || isPasswordEmpty;
        var validationRequired = addthis_credential_validation_status.val() == 0;

        if(isUsernameEmpty != isPasswordEmpty) {
            var emptyLabel = isUsernameEmpty ? "username" : "password";
            addthis_validation_message.html("&#x2716; AddThis " + emptyLabel + " is required to view analytics").next().hide();
            return false;
        } else if (isProfileEmpty && !isUsernameEmpty && !isPasswordEmpty) {
            addthis_profile_validation_message.html("&#x2716; AddThis profile ID is required to view analytics").next().hide();
            $('html, body').animate({"scrollTop":0}, 'slow');
            return false;
        } else if (!validationRequired || isAnyFieldEmpty) {
            return true;
        } else if(!isAnyFieldEmpty && validationRequired) {
            validate_addthis_credentials();
            return false;
        }
    });

    $("#addthis_username, #addthis_password, #addthis_profile").change(function(){
       addthis_credential_validation_status.val(0);
       if($.trim($("#addthis_profile").val()) == "") {
            addthis_profile_validation_message.next().hide();
       }
       if(($.trim($("#addthis_username").val()) == "") || ($.trim($("#addthis_password").val()) == "")) {
            addthis_validation_message.next().hide();
       }
    });

    $('.row-right a').mouseover(function(){
        var me = $(this),
        parent = $(me).parent(),
        dataContent = '',
        dataTitle = '',
        innerContent = '',
        left = 0,
        top = 0,
        popoverHeight = 0;

        dataContent = $(parent).attr('data-content');
        dataTitle = $(parent).attr('data-original-title');
        innerContent = "<div class='popover fade right in' style='display: block;'><div class='arrow'></div><h3 class='popover-title'>";
        innerContent =  innerContent + dataTitle;
        innerContent = innerContent + "</h3><div class='popover-content'>";
        innerContent = innerContent + dataContent;
        innerContent = innerContent + "</div></div>";
        $(parent).append(innerContent);

        var popoverHeight = $(parent).find('.popover').height(),
        left = $(me).position().left + 15,
        top = $(me).position().top - (popoverHeight/2) + 8;

        $(parent).find('.popover').css({
            'left': left+'px',
            'top': top+'px'
        });
    });

    $('.row-right a').mouseout(function(){
        $('.popover').remove();
    });
});

