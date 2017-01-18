$(function() {
  'use strict';

  // jshint newcap:false
  var formId = '#OperatingCentre';
  var F = OLCS.formHelper;

  var vehicles = F('data', 'noOfVehiclesRequired');
  var trailers = F('data', 'noOfTrailersRequired');

  var isVariation = vehicles.data('current');

  function hasAdvertisements() {
    return F.isChecked('advertisements', 'adPlaced', 'Y');
  }

  function isSendingByPost() {
    return F.isChecked('advertisements', 'adPlaced', 'N');
  }

  OLCS.cascadeForm({
    form: formId,
    cascade: false,
    rulesets: {
      'advertisements': {
        '*': function() {
          // this data attribute will only be set if we're a variation application...
          if (isVariation) {
            // in which case we show the advertisements section if the OC's auth has increased at all
            return vehicles.val() > vehicles.data('current') || trailers.val() > trailers.data('current');
          }

          // for non variations (i.e. licences and applications) we always show the ads block
          return true;
        },
        'label:adPlacedIn': hasAdvertisements,
        '.adPlacedDate': hasAdvertisements,
        '.file-uploader': hasAdvertisements,
        '.ad-send-by-post': isSendingByPost
      }
    }
  });

});
