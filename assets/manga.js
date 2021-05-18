import "./manga/ajax";

// Pour la recherche, on a doublons pour que les éléments fonctionnent toujours
import Filter from './manga/modules/Filter';

new Filter(document.querySelector('.js-filter'));
