import axios from "axios";
import { Toast } from 'bootstrap';

/**
 * @property {HTMLElement} content
 * @property {HTMLElement} pagination
 * @property {HTMLFormElement} form
 */
export default class Filter {

  /**
   *
   * @param {HTMLElement|null} element
   */
  constructor(element) {
    if (element === null) {
      return;
    }

    this.content = element.querySelector('.js-filter-content');
    this.pagination = element.querySelector('.js-filter-pagination');
    this.form = element.querySelector('.js-filter-form');
    this.loader = element.querySelector('#loader');

    this.bindEvents()
  }

  /**
   * Ajoute les comportements aux différents éléments
   */
  bindEvents() {

    if (this.pagination) {
      this.pagination.addEventListener('click', e => {
        if (e.target.tagName === 'A') {
          e.preventDefault();
          this.loadUrl(e.target.getAttribute('href'));
        }
      })
    }

    if (this.form) {
      this.form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => {
          clearTimeout(this.timeout);
          this.timeout = setTimeout(this.loadForm.bind(this), 1000);
        });
      })
    }
  }

  loadForm() {
    const data = new FormData(this.form);
    const url = new URL(this.form.getAttribute('action') || window.location.href);
    const params = new URLSearchParams();
    data.forEach((value, key) => {
      params.append(key, value);
    });

    this.loadUrl(url.pathname + '?' + params.toString());
  }

  loadUrl(url) {

    var _ = this;

    _.loader.style.display = 'block';
    axios.get(`${url}&ajax=1`).then(function (response) {
      if (_.currentContent !== response.data.content) {
        _.currentContent = response.data.content;

        _.content.innerHTML = response.data.content;

        _.content.querySelectorAll('a.twitter-action').forEach(function (link) {
          link.addEventListener('click', _.onClickLinkTwitterAction);
        });
        _.content.querySelectorAll('a.follow-action').forEach(function (link) {
          link.addEventListener('click', _.onClickLinkFollowAction);
        })
        _.content.querySelectorAll('a.refresh-action').forEach(function (link) {
          link.addEventListener('click', _.onClickLinkRefreshAction);
        })

      }

      if(_.pagination) {
        _.pagination.innerHTML = response.data.pagination;
      }

      history.replaceState({}, '', url);

    }).catch(function (error) {
      console.error(error);
    }).finally(() => {
      _.loader.style.display = 'none';
    })
  }

  onClickLinkTwitterAction(event) {
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

  onClickLinkFollowAction(event) {
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

  onClickLinkRefreshAction(event) {
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

}
