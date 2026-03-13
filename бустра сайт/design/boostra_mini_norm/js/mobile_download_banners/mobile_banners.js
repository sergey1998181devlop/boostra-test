//баннер мобильного приложения
document.addEventListener("DOMContentLoaded", () => {
  const bannersIOS = document.querySelectorAll(".download-mobile-banner.iphone");
  const bannersAndroid = document.querySelectorAll(".download-mobile-banner.android");
  function mobileBannerClose(banner) {
    if (banner === null || banner === undefined) {
      e.preventDefault();
    }
    if (banner === 'iPhone') {
      bannersIOS.forEach((el) => el.classList.add("mobileBanner_hidden"));
    }
    if (banner === 'Android') {
      bannersAndroid.forEach((el) => el.classList.add("mobileBanner_hidden"));
    }
  }

  if (navigator.userAgent.includes("iPhone")) {
    mobileBannerClose('Android');
  } /* else if (navigator.userAgent.includes("Android")) {
    mobileBannerClose('iPhone');
  } else {
    mobileBannerClose('iPhone');
    mobileBannerClose('Android');
  } */
});
