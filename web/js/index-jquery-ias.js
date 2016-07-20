// index page ias
var ias = jQuery.ias({
  container:  '.index',
  item:       '.spo-row',
  pagination: '.pagebar',
  next:       '.next a'
});
ias.extension(new IASPagingExtension());
// ias.extension(new IASTriggerExtension({
//     offset: 2,
//     html: $('.pagebar').html(),
// }));
ias.extension(new IASSpinnerExtension({}));