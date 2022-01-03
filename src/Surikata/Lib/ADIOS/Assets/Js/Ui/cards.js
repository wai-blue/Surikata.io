function ui_cards_refresh(uid) {
  if (uid == '') return;

  console.log(uid, $('#' + uid));

  _ajax_update(
    'UI/Cards',
    {'uid': uid, 'lpfs': 1},
    uid
  );
}