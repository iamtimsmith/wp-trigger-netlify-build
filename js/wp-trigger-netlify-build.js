jQuery(document).ready(function() {
  var netlify = "https://api.netlify.com/build_hooks/5c78b701a279bf117489dce1";
  //event handler for clicking the link button
  jQuery("#publish, #original_publish").on("click", function(e) {
    jQuery.ajax({
      type: "POST",
      url: netlify,
      success: function(d) {
        console.log(d);
      }
    });
  });
});
