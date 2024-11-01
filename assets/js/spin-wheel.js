(function () {

  const campaignName = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.campaign_name || 'Happy Offers';
  const campaignNameSlug = campaignName.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();

  const SpinWheel = {
    /**
     * Set Cookie in Browser
     */
    setCookie: function (name, value, days) {
      const nameEQ = campaignNameSlug + '_' + name;
      let expires = "";
      if (days) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = nameEQ + "=" + (value || "") + expires + "; path=/";
    },

    /**
     * Get Cookie from Browser
     */
    getCookie: function (name) {
      const nameEQ = campaignNameSlug + '_' + name + "=";
      const ca = document.cookie.split(";");
      for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === " ") c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
          return c.substring(nameEQ.length, c.length);
      }
      return null;
    },

    /**
     * Create and append the necessary images
     */
    createImages: function () {
      this.imgElement = this.createImageElement(
        "spin-wheel-img",
        WOF_LocalizeConfig.assetsUrl + "imgs/example-0-image.svg"
      );

      this.imgOverlayElement = this.createImageElement(
        "spin-wheel-img-overlay",
        WOF_LocalizeConfig.assetsUrl + "imgs/example-0-overlay.svg"
      );
    },

    /**
     * Utility function to create an image element
     */
    createImageElement: function (id, src) {
      const img = document.createElement("img");
      img.src = src;
      img.id = id;
      img.style.display = "none";
      document.body.appendChild(img);
      return img;
    },

    /**
     * Setup the wheel instance
     */
    setupWheel: function () {
      const container = document.querySelector("#spin-wheel");
      this.wheel = new spinWheel.Wheel(container, this.props);
    },

    /**
     * Bind event listeners
     */
    bindEvents: function () {
      const form = document.querySelector(".spin-wheel-forms-spin");
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const name = document.querySelector(
          '.spin-wheel-forms-spin input[name="name"]'
        ).value;
        const email = document.querySelector(
          '.spin-wheel-forms-spin input[name="email"]'
        ).value;
        const nonce = document.querySelector(
          '.spin-wheel-forms-spin input[name="_wpnonce"]'
        ).value;
        const campaign = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.campaign_name;

        if (name && email) {
          form.querySelector('button').setAttribute('disabled', 'disabled');
          document.querySelector(".spin-wheel-form-loader").classList.remove("spin-wheel-hidden");

          this.setCookie("wof_name", name, 15);
          this.setCookie("wof_email", email, 15);
          this.setCookie("wof_campaign", campaign, 15);
          this.setCookie("nonce", nonce, 15);

          this.handleSpinClick();
        }
      });
    },

    /**
     * Calculate the probability of winning
     */
    probability: function ($coupons) {
      /**
       * Setting default probability to a lower value
       */
      const defaultProbability = 1;

      /**
       * Set default probability if not specified and ensure probability is an integer
       */
      $coupons.forEach(coupon => {
        if (typeof coupon.probability === 'undefined' || coupon.probability === 0) {
          coupon.probability = defaultProbability;
        } else {
          coupon.probability = parseInt(coupon.probability, 10);  // Ensure it's an integer
        }
      });

      /**
       * Calculate the total probability to use for normalization
       */
      const totalProbability = $coupons.reduce((sum, item) => sum + item.probability, 0);

      /**
       * Create cumulative probability array
       */
      let cumulativeProbability = 0;
      const cumulative = $coupons.map((item, index) => {
        cumulativeProbability += item.probability / totalProbability;  // Normalize the probability
        return { label: item.label, cumulativeProbability, index };  // Store the index for later
      });

      /**
       * Generate a random number between 0 and 1
       */
      const random = Math.random();

      /**
       * Find the corresponding item based on the random number
       */
      for (let item of cumulative) {
        if (random <= item.cumulativeProbability) {
          return {
            selectedCoupon: item.label,
            selectedIndex: item.index
          };
        }
      }

      return null;
    },

    /**
     * Handle spin button click
     */
    handleSpinClick: function () {
      const { duration, winningItemRotaion } = this.calcSpinToValues();
      const probalilityUsed = WOF_LocalizeConfig?.wheel_data?.coupons?.some(coupon => {
        return coupon.probability;
      });

      if (true == probalilityUsed) {
        let winningItemIndex = this.probability(WOF_LocalizeConfig?.wheel_data?.coupons).selectedIndex;
        this.wheel.spinToItem(winningItemIndex, duration, true, 2, 1, easingFunction = null);
      } else {
        this.wheel.spinTo(winningItemRotaion, duration);
      }
    },

    /**
     * Calculate spin to values
     */
    calcSpinToValues: function () {
      const duration = 3000;
      const winningItemRotaion = this.getRandomInt(360, 360 * 1.75) + this.modifier;
      this.modifier += 360 * 1.75;
      return { duration, winningItemRotaion };
    },

    /**
     * Utility function to get random integer
     */
    getRandomInt: function (min, max) {
      min = Math.ceil(min);
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min)) + min;
    },

    /**
     * Define properties for the wheel
     */
    getProps: function () {
      const activeCoupons = WOF_LocalizeConfig?.wheel_data?.coupons || [];
      var bgItems = WOF_LocalizeConfig?.wheel_data?.wheelStyles.bgItems || false;
      bgItems = false !== bgItems && bgItems?.map((item) => {
        delete item.label;
        return item.value;
      });

      if (!bgItems.length) {
        bgItems = ['#b21b00', '#f8aa02', '#f67b05', '#f7994c', '#f24101'];
      }

      return {
        radius: 0.84,
        itemLabelRadius: 0.93,
        itemLabelRadiusMax: 0.35,
        itemLabelRotation: 180,
        itemLabelColors: ["#fff"],
        itemLabelAlign: "left",
        itemLabelBaselineOffset: -0.07,
        // itemLabelFont: 'Amatic SC',
        itemLabelFontSizeMax: 20,
        itemBackgroundColors: bgItems,
        rotationSpeedMax: 500,
        rotationResistance: -100,
        lineWidth: 0,
        lineColor: "#fff",
        image: this.imgElement,
        overlayImage: this.imgOverlayElement,
        isInteractive: false,
        items: activeCoupons,
      };
    },

    /**
     * Copy to Clipboard
     */
    copyToClipboard: function (text) {
      const el = document.createElement("textarea");
      el.value = text;
      document.body.appendChild(el);
      el.select();
      document.execCommand("copy");
      document.body.removeChild(el);
    },

    /**
     * Manage Popups
     */
    managePopups: function (data) {
      if (true == data?.win) {
        let label = data.label;
        let couponCode = data.value;
        document.querySelector(".spin-wheel-spin-step").remove();
        document.querySelector(".spin-wheel-form-loader").remove();
        let winDom = document.querySelector(".spin-wheel-win-step");
        // remove the hidden class spin-wheel-hidden
        winDom.classList.remove("spin-wheel-hidden");

        /**
         * Set the cookie to prevent the popup from showing again
         */
        let reappearTime = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.reappear_time || 1;
        SpinWheel.setCookie("spin_wheel_popup_closed", true, reappearTime);

        /**
         * Set the coupon label
         * Hey, You got {{prize}}
         * Hey, You got 20% Off
         */
        let content_label = winDom.querySelector("h2");
        content_label.innerHTML = content_label.innerHTML.replace('{{prize}}', label);

        if (data.email_coupon) {
          document.querySelector(".spin-wheel-win-coupon-wrap").remove();
          document.querySelector(".spin-wheel-win-step p").innerHTML = "Coupon has been sent to your email address.";
          return;
        }

        /**
         * Set the coupon code
         */
        winDom.querySelector("#spin-wheel-win-coupon").innerHTML = couponCode;

        /**
         * Copy the coupon code to clipboard
         */
        winDom.querySelector(".spin-wheel-win-coupon-wrap button").addEventListener("click", () => {
          this.copyToClipboard(couponCode);
          winDom.querySelector(".spin-wheel-win-coupon-wrap button").innerHTML = "Copied!";
        });
      } else {
        console.log(data);
        document.querySelector(".spin-wheel-spin-step").remove();
        document.querySelector(".spin-wheel-form-loader").remove();
        document.querySelector(".spin-wheel-win-coupon-wrap").remove();
        let winDom = document.querySelector(".spin-wheel-win-step");
        // remove the hidden class spin-wheel-hidden
        winDom.classList.remove("spin-wheel-hidden");
        if (data?.error === 'already_revealed') {
          winDom.querySelector("h2").innerHTML = "Oops, you have already revealed the coupon.";
        }
        winDom.querySelector("p").innerHTML = "";

        /**
         * Set the cookie to prevent the popup from showing again
         */
        let reappearTime = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.reappear_time || 1;
        SpinWheel.setCookie("spin_wheel_popup_closed", true, reappearTime);
      }
    },

    /**
     * Reveals the Coupon Code from API
     */
    revealCouponCode: async function (couponId) {
      /**
       * Prepare the request URL and data
       */
      let url = WOF_LocalizeConfig.ajaxurl;

      /**
       * Initialize a new XMLHttpRequest
       */
      let xhr = new XMLHttpRequest();

      /**
       * Open a new POST request to 'admin-ajax.php'
       */
      xhr.open('POST', url, true);

      /**
       * Set the appropriate headers
       */
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Form-encoded for admin-ajax

      /**
       * Handle the response
       */
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            /**
             * Parse and log the response
             */
            let response = JSON.parse(xhr.responseText);
            SpinWheel.managePopups(response?.data?.data || response?.data);
          } else {
            console.error('Error:', xhr.status, xhr.statusText);
          }
        }
      };

      /**
       * Prepare the data to be sent in a form-encoded format
       */
      const campaign = SpinWheel.getCookie("wof_campaign");
      const email = SpinWheel.getCookie("wof_email");
      const name = SpinWheel.getCookie("wof_name");
      const nonce = WOF_LocalizeConfig.nonce;

      // let data = 'action=reveal_coupon&couponID=' + encodeURIComponent(couponId) + '&nonce=' + encodeURIComponent(WOF_LocalizeConfig.nonce);
      let data = new URLSearchParams();
      data.append('action', 'reveal_coupon');
      data.append('couponID', couponId);
      data.append('nonce', nonce);
      data.append('email', email);
      data.append('name', name);
      data.append('campaign', campaign);

      /**
       * Send the request
       */
      xhr.send(data);

    },
    /**
     * Get the events on rest
     */
    onRestEvent: function (e) {
      let currentIndex = e.currentIndex;
      setTimeout(() => {
        this.revealCouponCode(currentIndex);
      }, 500);
    },

    /**
     * Fire the Wheel
     */
    showPopup: function () {
      const popup = document.querySelector(".spin-wheel-wrapper");

      if (popup) {
        MicroModal.show('wof__modal');

        /**
         * Set the cookie to prevent the popup from showing again
         */
        let reappearTime = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.reappear_time || 1;
        this.setCookie("spin_wheel_popup_closed", true, reappearTime);
      }
    },

    /**
     * Visibile on Condition
     */
    isVisible: function () {
      const visibility = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility;

      /**
       * Show On Page Load
       */
      if ("on_page_load" === visibility?.display_option) {
        const delay = visibility?.show_after_time * 1000 || 500;
        setTimeout(() => {
          this.showPopup();
        }, delay);
      }

      /**
       * Show on Exit
       */
      if ("on_exit" === visibility?.display_option) {
        document.addEventListener("mouseleave", () => {
          this.showPopup();
        });
      }

      /**
       * Show on Scroll Percentage
       */
      if ("on_scroll" === visibility?.display_option) {
        window.addEventListener("scroll", () => {
          let scrollPercentage = Math.round(
            ((document.documentElement.scrollTop + document.body.scrollTop) /
              (document.documentElement.scrollHeight -
                document.documentElement.clientHeight)) *
            100
          );
          if (scrollPercentage >= visibility?.show_on_scroll_percent) {
            SpinWheel.showPopup();
          }
        });
      }

      /**
       * On Click Event Class/ID
       */
      if ("on_click" === visibility?.display_option && true == WOF_LocalizeConfig?.isPro) {
        let btn = document.querySelector(visibility?.on_click_selectors);
        if (btn) {
          btn.addEventListener("click", () => {
            SpinWheel.showPopup();
          });
        }
      }

      /**
       * Show Based on Inactivity
       */

      let inactivityTimeout;
      const inactivityTime = visibility?.show_on_inactivity_time * 1000 || 5000;

      if ("on_inactivity" === visibility?.display_option) {
        const resetInactivityTimeout = () => {
          clearTimeout(inactivityTimeout);
          inactivityTimeout = setTimeout(() => {
            /**
             * Perform the action you want to show based on inactivity
             */
            SpinWheel.showPopup();
          }, inactivityTime);
        };

        document.addEventListener("mousemove", resetInactivityTimeout);

        /**
         * Initialize the inactivity timeout
         */
        resetInactivityTimeout();
      }

      /**
       * Referrer
       */
      if ("on_referrer" === visibility?.display_option && true == WOF_LocalizeConfig?.isPro) {
        const referrer = visibility?.referrer_contains || '';
        if (document.referrer.includes(referrer)) {
          SpinWheel.showPopup();
        }
      }

      return true;
    },
    /**
     * Styles All Elements
     */
    setStyles: function () {
      const submitForm = WOF_LocalizeConfig?.wheel_data?.wheelStyles?.submit_form || false;
      const winInfo = WOF_LocalizeConfig?.wheel_data?.wheelStyles?.win_info || false;

      if (submitForm) {
        let $title = document.querySelector(".spin-wheel-forms-spin h2");
        let $desc = document.querySelector(".spin-wheel-forms-spin p");
        let $input = document.querySelectorAll(".spin-wheel-forms-spin input");
        let $button = document.querySelector(".spin-wheel-forms-spin button");

        if (submitForm.title) {
          $title.innerHTML = submitForm.title;
        }
        if (submitForm.titleAlignment) {
          $title.style.textAlign = submitForm.titleAlignment;
        }
        if (submitForm.titleColor) {
          $title.style.color = submitForm.titleColor;
        }
        if (submitForm.titleSize) {
          $title.style.fontSize = submitForm.titleSize;
        }
        if (submitForm.desc) {
          $desc.innerHTML = submitForm.desc;
        }
        if (submitForm.descAlignment) {
          $desc.style.textAlign = submitForm.descAlignment;
        }
        if (submitForm.descColor) {
          $desc.style.color = submitForm.descColor;
        }
        if (submitForm.descSize) {
          $desc.style.fontSize = submitForm.descSize;
        }
        if (submitForm.inputColor) {
          $input.forEach((input) => {
            input.style.color = submitForm.inputColor;
          });
        }
        if (submitForm.inputBg) {
          $input.forEach((input) => {
            input.style.backgroundColor = submitForm.inputBg;
          });
        }
        if (submitForm.inputBorderColor) {
          $input.forEach((input) => {
            input.style.borderColor = submitForm.inputBorderColor;
          });
        }
        if (submitForm.inputBorderRadius) {
          $input.forEach((input) => {
            input.style.borderRadius = submitForm.inputBorderRadius;
          });
        }
        if (submitForm.inputSize) {
          $input.forEach((input) => {
            input.style.fontSize = submitForm.inputSize;
          });
        }
        if (submitForm.buttonAlignment) {
          $button.style.textAlign = submitForm.buttonAlignment;
          if ('justify' === submitForm.buttonAlignment) {
            $button.style.width = '100%';
            $button.style.textAlign = 'center';
          }
        }
        if (submitForm.buttonBackground) {
          $button.style.backgroundColor = submitForm.buttonBackground;
        }
        if (submitForm.buttonBorderColor) {
          $button.style.borderColor = submitForm.buttonBorderColor;
        }
        if (submitForm.buttonColor) {
          $button.style.color = submitForm.buttonColor;
        }
        if (submitForm.buttonFontSize) {
          $button.style.fontSize = submitForm.buttonFontSize;
        }

      }

      if (winInfo) {
        let $title = document.querySelector(".spin-wheel-win-step h2");
        let $desc = document.querySelector(".spin-wheel-win-step p");
        let $input = document.querySelector(".spin-wheel-win-coupon-wrap pre");
        let $button = document.querySelector(".spin-wheel-win-step button");

        if (winInfo.title) {
          $title.innerHTML = winInfo.title;
        }
        if (winInfo.titleAlignment) {
          $title.style.textAlign = winInfo.titleAlignment;
        }
        if (winInfo.titleColor) {
          $title.style.color = winInfo.titleColor;
        }
        if (winInfo.titleSize) {
          $title.style.fontSize = winInfo.titleSize;
        }
        if (winInfo.desc) {
          $desc.innerHTML = winInfo.desc;
        }
        if (winInfo.descAlignment) {
          $desc.style.textAlign = winInfo.descAlignment;
        }
        if (winInfo.descColor) {
          $desc.style.color = winInfo.descColor;
        }
        if (winInfo.descSize) {
          $desc.style.fontSize = winInfo.descSize;
        }
        if (winInfo.inputColor) {
          $input.style.color = winInfo.inputColor;
        }
        if (winInfo.inputBorderColor) {
          $input.style.borderColor = winInfo.inputBorderColor;
        }
        if (winInfo.inputBackground) {
          $input.style.backgroundColor = winInfo.inputBackground;
        }
        if (winInfo.inputSize) {
          $input.style.fontSize = winInfo.inputSize;
        }
        if (winInfo.buttonColor) {
          $button.style.color = winInfo.buttonColor;
        }
        if (winInfo.buttonBackground) {
          $button.style.backgroundColor = winInfo.buttonBackground;
        }
        if (winInfo.buttonBorderColor) {
          $button.style.borderColor = winInfo.buttonBorderColor;
        }
        if (winInfo.buttonFontSize) {
          $button.style.fontSize = winInfo.buttonFontSize;
        }
      }
    },

    /**
     * Cookie Validation
     */
    validateCookie: function () {
      if (true === WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.dev_mode) {
        return true;
      }
      if (this.getCookie("spin_wheel_popup_closed")) {
        return false;
      }
      // if (this.getCookie("spin_wheel_popup_count")) {
      //   return false;
      // }
      if (this.getCookie("spin_wheel_popup_dont_feel_lucky")) {
        return false;
      }
      return true;
    },

    /**
     * Initialize the SpinWheel
     */
    init: function () {

      if (!this.validateCookie()) {
        return;
      }

      document.querySelector(".spin-wheel-close-icon").addEventListener("click", () => {
        MicroModal.close('wof__modal');

        /**
         * Set the cookie to prevent the popup from showing again
         */
        let reappearTime = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility?.reappear_time || 1;
        SpinWheel.setCookie("spin_wheel_popup_closed", true, reappearTime);
      });

      this.createImages();
      this.props = this.getProps();
      this.modifier = 0;
      this.setupWheel();
      this.bindEvents();
      // Log events for easy debugging:
      this.wheel.onRest = (e) => this.onRestEvent(e);

      /**
       * First Checked by Server Side
       * Second Checked by Front End
       *
       * Hide the Panel instead destroying the SpinWheel
       */
      this.isVisible();

      /**
       * Set whole styles withi Cookie
       */
      this.setStyles();

    },
  };

  document.addEventListener("DOMContentLoaded", function () {
    /**
     * WordPress default breakpoints
     */
    const visibility = WOF_LocalizeConfig?.wheel_data?.frontEndVisibility;
    const isDesktop = window.matchMedia('(min-width: 1025px)').matches;
    const isTablet = window.matchMedia('(min-width: 768px) and (max-width: 1024px)').matches;
    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    /**
     * Show modal based on visibility settings and screen size
     */
    if (
      (visibility?.visibility_desktop && isDesktop) ||
      (visibility?.visibility_tablet && isTablet) ||
      (visibility?.visibility_mobile && isMobile)
    ) {
      SpinWheel.init();
    }
  });

})();
