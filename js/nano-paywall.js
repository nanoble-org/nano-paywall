(function ($) {
  var get_content = function(result) {

      var json_result = JSON.parse(result);
      if( json_result.success == 1 && json_result.content.indexOf("nano-button") == -1 ) {
        $(".np-container").slideUp( "slow", function() {
          $(".np-content").html(json_result.content);
            $(".np-overlay").hide();
        });

        $(".np-container").slideDown( "slow", function() {});
            
      } else {
        $.post( nano_paywall_object.ajaxurl, { action: "get_content", token: data.token, security: nano_paywall_object.security } )
            .done( get_content )
            .fail(function () {
                $(".np-content").append("<div style=\"color:red; margin:0 auto; width:50%;\">An error has occurred. Please refresh and try again.</div>");
            });
      }
  }

  $(".np-info-button").click(function() {
    $(".np-info").toggle();
  });

  // Render the pay Nano button
  brainblocks.Button.render({

    // Pass in payment options
    payment: {
      currency: "rai",
      amount: nano_paywall_object.amount,
      destination: nano_paywall_object.address
    },

    // Handle successful payments
    onPayment: function(data) {
      $.post( nano_paywall_object.ajaxurl, { action: "get_content", token: data.token, amount: nano_paywall_object.amount, address: nano_paywall_object.address, security: nano_paywall_object.security } )
          .done( get_content )
          .fail(function () {
              $(".np-content").append("<div style=\"color:red; margin:0 auto; width:50%;\">An error has occurred. Please refresh and try again.</div>");
      });
    }
  }, "#nano-button");
})(jQuery);