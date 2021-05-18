import axios from "axios";
import { Toast } from 'bootstrap';

function onClickLinkTwitterAction(event) {
  event.preventDefault();
  const url = this.href;
  const link = this;

  const spinner = link.parentNode.parentNode.getElementsByClassName('spinner');

  if (spinner.length > 0) {
    spinner[0].style.display = 'block';
  }

  const toastContainer = document.querySelector('#toastContainer');
  axios.post(url).then(function (response) {

    spinner[0].style.display = 'none';

    link.textContent = response.data.value;

    if (link.classList.contains('twitter-enabled')) {
      link.classList.replace('twitter-enabled', 'twitter-disabled');
    } else {
      link.classList.replace('twitter-disabled', 'twitter-enabled');
    }

    toastContainer.innerHTML = response.data.content;
    var bsAlert = new Toast(document.getElementById('toastAlert'));//inizialize it
    bsAlert.show();//show it
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

  const spinner = link.parentNode.parentNode.getElementsByClassName('spinner');

  if (spinner.length > 0) {
    spinner[0].style.display = 'block';
  }

  const toastContainer = document.querySelector('#toastContainer');
  axios.post(url).then(function (response) {

    spinner[0].style.display = 'none';

    link.textContent = response.data.value;

    if (link.classList.contains('followed')) {
      link.classList.replace('followed', 'unfollowed');
    } else {
      link.classList.replace('unfollowed', 'followed');
    }

    toastContainer.innerHTML = response.data.content;
    var bsAlert = new Toast(document.getElementById('toastAlert'));//inizialize it
    bsAlert.show();//show it
  }).catch(function (error) {

  })
}

document.querySelectorAll('a.follow-action').forEach(function (link) {
  link.addEventListener('click', onClickLinkFollowAction);
})

function onClickLinkRefreshAction(event) {
  event.preventDefault();
  const url = this.href;
  const link = this;

  const spinner = link.parentNode.parentNode.getElementsByClassName('spinner');

  if (spinner.length > 0) {
    spinner[0].style.display = 'block';
  }

  const toastContainer = document.querySelector('#toastContainer');
  axios.post(url).then(function (response) {
    spinner[0].style.display = 'none';

    toastContainer.innerHTML = response.data.content;
    var bsAlert = new Toast(document.getElementById('toastAlert'));//inizialize it
    bsAlert.show();//show it
  }).catch(function (error) {

  })
}

document.querySelectorAll('a.refresh-action').forEach(function (link) {
  link.addEventListener('click', onClickLinkRefreshAction);
})
