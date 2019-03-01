jQuery(document).ready(
  (function($) {
    // event handler for clicking the link button
    $("#publish, #original_publish").on("click", function(e) {
      $.ajax({
        type: "POST",
        url: wpTriggerNetlifyBuildVars,
        success: function(d) {
          console.log(d);
        }
      });
    });
  })(jQuery)
);
