(window.webpackJsonp=window.webpackJsonp||[]).push([["manga"],{oBu8:function(t,e,n){"use strict";n.r(e);n("QWBl"),n("rB9j"),n("UxlC"),n("FZtP");var a=n("vDqi"),l=n.n(a);function o(t){t.preventDefault();var e=this.href,n=this;l.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("twitter-enabled")?n.classList.replace("twitter-enabled","twitter-disabled"):n.classList.replace("twitter-disabled","twitter-enabled")})).catch((function(t){}))}function c(t){t.preventDefault();var e=this.href,n=this;l.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("followed")?n.classList.replace("followed","unfollowed"):n.classList.replace("unfollowed","followed")})).catch((function(t){}))}document.querySelectorAll("a.twitter-action").forEach((function(t){t.addEventListener("click",o)})),document.querySelectorAll("a.follow-action").forEach((function(t){t.addEventListener("click",c)}))}},[["oBu8","runtime",2]]]);