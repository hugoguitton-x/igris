import axios from "axios";

function onClickLinkTwitterAction(event) {
  event.preventDefault();
  const url = this.href;
  const link = this;

  axios.post(url).then(function (response) {

    link.textContent = response.data.value;

    if (link.classList.contains('twitter-enabled')) {
      link.classList.replace('twitter-enabled', 'twitter-disabled');
    } else {
      link.classList.replace('twitter-disabled', 'twitter-enabled');
    }
  }).catch(function (error) {

  })
}

document.querySelectorAll('a.twitter-action').forEach(function (link) {
  link.addEventListener('click', onClickLinkTwitterAction);
});


function onClickLinkFollowAction(event) {
  event.preventDefault();
  const url = this.href;
  const link = this;

  axios.post(url).then(function (response) {

    link.textContent = response.data.value;

    if (link.classList.contains('followed')) {
      link.classList.replace('followed', 'unfollowed');
    } else {
      link.classList.replace('unfollowed', 'followed');
    }
  }).catch(function (error) {

  })
}

document.querySelectorAll('a.follow-action').forEach(function (link) {
  link.addEventListener('click', onClickLinkFollowAction);
})

