import axios from "axios";


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
    this.form = element.querySelector('.js-filter-form');
    this.loader = element.querySelector('div[unique="true"]');

    this.bindEvents()
  }

  /**
   * Ajoute les comportements aux différents éléments
   */
  bindEvents() {

    this.form.querySelectorAll('input').forEach(input => {
      input.addEventListener('change', this.loadForm.bind(this));
    })
    this.form.querySelectorAll('select').forEach(select => {
      select.addEventListener('change', this.loadForm.bind(this));
    })

  }

  loadForm() {
    const data = new FormData(this.form);
    const url = new URL(this.form.getAttribute('action') || window.location.href);
    const params = new URLSearchParams();
    data.forEach((value, key) => {
      value = (key == "date[day]") ? 1 : value;
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

      }

      history.replaceState({}, '', url);

    }).catch(function (error) {
      console.error(error);
    }).finally(() => {
      _.loader.style.display = 'none';
    })
  }


}
