// index page ias
var ias = jQuery.ias({
  container:  '.index',
  item:       '.spo-row',
  pagination: '.pagebar',
  next:       '.next a'
});
ias.extension(new IASPagingExtension());
ias.on('pageChange', function(pageNum, scrollOffset, url) {
    console.log(
        "Welcome at page " + pageNum + ", " +
        "the original url of this page would be: " + url
    );
    // $('.pagination .active').addClass('aaa');
    // console.log(.removeClass('active').next().addClass('active'));
});
ias.extension(new IASTriggerExtension({
    offset: 2,
    html: $('.pagebar').html(),
}));
ias.extension(new IASSpinnerExtension({}));
