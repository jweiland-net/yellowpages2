// initialize dialog box
jQuery("#dialogHint").dialog({
    autoOpen: false, height: 150, width: 300, modal: true
});

// show dialog box on click
jQuery("span.csh")
  .css("cursor", "pointer")
  .click(function () {
      var property = jQuery(this).data("property");
      jQuery("#dialogHint p").text(jQuery("#hidden_" + property).text());
      jQuery("#dialogHint").dialog("open");
  });