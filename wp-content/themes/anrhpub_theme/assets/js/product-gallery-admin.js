(function ($) {
  'use strict';

  var cfg = window.anrhpubGalleryAdmin || {};
  var i18n = cfg.i18n || {};

  function parseIds(val) {
    if (!val) {
      return [];
    }
    return val
      .split(',')
      .map(function (x) {
        return parseInt(x, 10);
      })
      .filter(Boolean);
  }

  function renderPreview(ids) {
    var $list = $('#anrhpub-gallery-preview');
    var $input = $('#anr_product_gallery_ids');
    if (!$list.length || !$input.length) {
      return;
    }

    $list.empty();
    ids.forEach(function (id, index) {
      var attachment = wp.media.attachment(id);
      attachment.fetch().then(function () {
        var url = attachment.get('sizes') && attachment.get('sizes').thumbnail
          ? attachment.get('sizes').thumbnail.url
          : attachment.get('url');
        if (!url) {
          return;
        }
        var $li = $('<li class="anrhpub-gallery-preview__item" />').attr('data-id', id);
        $li.append($('<img />').attr({ src: url, alt: '', width: 60, height: 60 }));
        if (index === 0) {
          $li.append(
            $('<span class="anrhpub-gallery-preview__badge" />').text(i18n.main || 'Principale')
          );
        }
        $li.append(
          $('<button type="button" class="anrhpub-gallery-preview__remove" aria-label="Retirer" />').html('&times;')
        );
        $list.append($li);
      });
    });

    $input.val(ids.join(','));
  }

  $(function () {
    var $input = $('#anr_product_gallery_ids');
    if (!$input.length) {
      return;
    }

    var frame;

    $('#anrhpub-gallery-add').on('click', function (e) {
      e.preventDefault();

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: i18n.title || 'Images du produit',
        button: { text: i18n.select || 'Utiliser ces images' },
        multiple: true,
        library: { type: 'image' }
      });

      frame.on('open', function () {
        var selection = frame.state().get('selection');
        var ids = parseIds($input.val());
        ids.forEach(function (id) {
          var att = wp.media.attachment(id);
          att.fetch();
          selection.add(att);
        });
      });

      frame.on('select', function () {
        var ids = [];
        frame.state().get('selection').each(function (att) {
          ids.push(att.id);
        });
        renderPreview(ids);
      });

      frame.open();
    });

    $('#anrhpub-gallery-preview').on('click', '.anrhpub-gallery-preview__remove', function () {
      var id = parseInt($(this).closest('[data-id]').attr('data-id'), 10);
      var ids = parseIds($input.val()).filter(function (x) {
        return x !== id;
      });
      renderPreview(ids);
    });
  });
})(jQuery);
