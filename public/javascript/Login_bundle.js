(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["Login"],{

/***/ "./browser/src/components/Login.jsx":
/*!******************************************!*\
  !*** ./browser/src/components/Login.jsx ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Login; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Modal.jsx */ "./browser/src/components/Modal.jsx");
/* harmony import */ var _UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./UnderlinedInput.jsx */ "./browser/src/components/UnderlinedInput.jsx");
/* harmony import */ var _NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./NavMenu.jsx */ "./browser/src/components/NavMenu.jsx");
/* harmony import */ var _lib_icons_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../lib/icons.js */ "./browser/src/lib/icons.js");
/* eslint-disable no-prototype-builtins */

/**
 * Login component with login form
 * Note: Component is lazy loaded upon clicking login button
 */





console.log('<Login> has been lazy loaded!');
const API_ENDPOINT = `${"/api"}/login`;
const initialState = {
  isOpen: true,
  isLoading: false,
  isError: false,
  mode: 'login',
  // login; register
  current: 'username',
  // Form field to fill... username, password, email
  user: {},
  // user credentials: username, email, password
  error: {}
};

const reducer = (state, action) => {
  switch (action.type) {
    case 'TOGGLE':
      return { ...initialState,
        isOpen: !state.isOpen
      };

    case 'INIT':
      return { ...state,
        isLoading: true,
        isError: false
      };

    case 'SUBMIT':
      return { ...state,
        isLoading: false,
        isError: false,
        current: action.current,
        user: { ...state.user,
          [action.inputName]: action.inputValue
        }
      };

    case 'REGISTER':
      return { ...state,
        isLoading: false,
        isError: false,
        mode: 'register'
      };

    case 'LOGIN_SUCCESS':
      window.location.reload();
      return { ...state,
        isLoading: false,
        isError: false
      };

    case 'LOGIN_ERROR':
      return { ...state,
        isLoading: false,
        isError: true,
        error: action.error ? action.error : {
          message: 'An error occurred.'
        }
      };

    default:
      throw new Error();
  }
};

function Login() {
  const form = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();
  const [state, dispatchState] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useReducer(reducer, initialState);

  const toggleOpen = event => {
    event.preventDefault();
    dispatchState({
      type: 'TOGGLE'
    });
  };

  const handleSubmit = async event => {
    event.preventDefault();
    dispatchState({
      type: 'INIT'
    });
    const input = form['current'][state.current]['value'];

    if (state.current === 'username') {
      const response = await fetch(`${API_ENDPOINT}/${input}`, {
        method: 'GET',
        mode: 'same-origin',
        credentials: 'same-origin'
      });

      if (response.ok) {
        const result = await response.json();
        dispatchState({
          type: 'SUBMIT',
          inputName: 'username',
          inputValue: result.collection.items[0].username,
          current: 'password'
        });
      } else {
        dispatchState({
          type: 'REGISTER'
        });
      }
    } else if (state.current === 'password') {
      const payload = { ...state.user,
        password: input
      };
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      if (response.ok) {
        dispatchState({
          type: 'LOGIN_SUCCESS'
        });
      } else {
        const result = await response.json();
        console.log(response, result);
        dispatchState({
          type: 'LOGIN_ERROR',
          error: result.collection.errors[0]
        });
      }
    } else {
      dispatchState({
        type: 'LOGIN_ERROR',
        error: 'An unknown error occurred.'
      });
    }
  };

  const LoginButton = () => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php",
    title: "Login",
    onClick: toggleOpen,
    className: "user user-unknown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_4__["QuestionBlock"], {
    className: "user-avatar thumbnail"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, "Login"));

  let message;

  if (state.isError) {
    message = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      className: "error"
    }, state.error.hasOwnProperty('message') ? state.error.message : 'An error occurred');
  } else if (state.mode === 'login') {
    if (state.current === 'username') message = "Erm... What's your name again?";else if (state.current === 'password') message = `That's right! I remember now! Your name is ${state.user.username}!`;
  } else if (state.mode === 'register') {
    message = 'We don\'t have anyone registered by that name. Would you like to register?';
  }

  const LoginForm = () => {
    if (state.current === 'username') {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
        name: "username",
        placeholder: "Username or Email",
        padding: 19,
        autofocus: true
      });
    }

    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
      type: "password",
      name: "password",
      placeholder: "Password",
      padding: 19,
      autofocus: true
    });
  };

  const RegisterForm = () => 'Register form here....';

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginButton, null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: state.isOpen,
    close: toggleOpen
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("form", {
    id: "loginform",
    ref: form,
    onSubmit: handleSubmit,
    className: state.isLoading ? 'loading' : undefined
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-nav"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, {
    selected: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#login"
  }, "New Name")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "Blue")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "Gary")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "John")))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-rival"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-message"
  }, message), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-input"
  }, state.mode === 'login' ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginForm, null) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(RegisterForm, null)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-submit"
  }, state.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_4__["LoadingMascot"], {
    className: "loading"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, "Professor Oak is thinking")) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "submit",
    disabled: state.isLoading
  }, "Submit")))));
}

/***/ }),

/***/ "./browser/src/components/UnderlinedInput.jsx":
/*!****************************************************!*\
  !*** ./browser/src/components/UnderlinedInput.jsx ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return UnderlinedInput; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }


const typography = {
  fontFamily: 'Press Start'
};
function UnderlinedInput({
  value = '',
  type = 'text',
  padding = 10,
  autofocus,
  ...props
}) {
  const [state, setState] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(value);

  const handleChange = event => {
    setState(event.target.value);
  };

  const placeholderPad = padding - state.length;
  const placeholder = '_'.repeat(placeholderPad > 0 ? placeholderPad : 0);

  if (autofocus) {
    props.ref = input => input && input.focus();
  }

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "underlinedinput",
    style: {
      position: 'relative'
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "underline",
    style: {
      position: 'absolute',
      left: 0,
      top: '.3em',
      zIndex: 0,
      ...typography
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    style: {
      opacity: 0
    }
  }, state), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, placeholder)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", _extends({
    type: type,
    value: state,
    onChange: handleChange,
    style: {
      position: 'relative',
      zIndex: 1,
      ...typography
    }
  }, props)));
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0xvZ2luLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1VuZGVybGluZWRJbnB1dC5qc3giXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsIkFQSV9FTkRQT0lOVCIsInByb2Nlc3MiLCJpbml0aWFsU3RhdGUiLCJpc09wZW4iLCJpc0xvYWRpbmciLCJpc0Vycm9yIiwibW9kZSIsImN1cnJlbnQiLCJ1c2VyIiwiZXJyb3IiLCJyZWR1Y2VyIiwic3RhdGUiLCJhY3Rpb24iLCJ0eXBlIiwiaW5wdXROYW1lIiwiaW5wdXRWYWx1ZSIsIndpbmRvdyIsImxvY2F0aW9uIiwicmVsb2FkIiwibWVzc2FnZSIsIkVycm9yIiwiTG9naW4iLCJmb3JtIiwiUmVhY3QiLCJ1c2VSZWYiLCJkaXNwYXRjaFN0YXRlIiwidXNlUmVkdWNlciIsInRvZ2dsZU9wZW4iLCJldmVudCIsInByZXZlbnREZWZhdWx0IiwiaGFuZGxlU3VibWl0IiwiaW5wdXQiLCJyZXNwb25zZSIsImZldGNoIiwibWV0aG9kIiwiY3JlZGVudGlhbHMiLCJvayIsInJlc3VsdCIsImpzb24iLCJjb2xsZWN0aW9uIiwiaXRlbXMiLCJ1c2VybmFtZSIsInBheWxvYWQiLCJwYXNzd29yZCIsImhlYWRlcnMiLCJib2R5IiwiSlNPTiIsInN0cmluZ2lmeSIsImVycm9ycyIsIkxvZ2luQnV0dG9uIiwiaGFzT3duUHJvcGVydHkiLCJMb2dpbkZvcm0iLCJSZWdpc3RlckZvcm0iLCJ1bmRlZmluZWQiLCJ0eXBvZ3JhcGh5IiwiZm9udEZhbWlseSIsIlVuZGVybGluZWRJbnB1dCIsInZhbHVlIiwicGFkZGluZyIsImF1dG9mb2N1cyIsInByb3BzIiwic2V0U3RhdGUiLCJ1c2VTdGF0ZSIsImhhbmRsZUNoYW5nZSIsInRhcmdldCIsInBsYWNlaG9sZGVyUGFkIiwibGVuZ3RoIiwicGxhY2Vob2xkZXIiLCJyZXBlYXQiLCJyZWYi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OiJMb2dpbl9idW5kbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiBlc2xpbnQtZGlzYWJsZSBuby1wcm90b3R5cGUtYnVpbHRpbnMgKi9cblxuLyoqXG4gKiBMb2dpbiBjb21wb25lbnQgd2l0aCBsb2dpbiBmb3JtXG4gKiBOb3RlOiBDb21wb25lbnQgaXMgbGF6eSBsb2FkZWQgdXBvbiBjbGlja2luZyBsb2dpbiBidXR0b25cbiAqL1xuXG5pbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IE1vZGFsIGZyb20gJy4vTW9kYWwuanN4JztcbmltcG9ydCBVbmRlcmxpbmVkSW5wdXQgZnJvbSAnLi9VbmRlcmxpbmVkSW5wdXQuanN4JztcbmltcG9ydCBOYXZNZW51IGZyb20gJy4vTmF2TWVudS5qc3gnO1xuaW1wb3J0IHsgUXVlc3Rpb25CbG9jaywgTG9hZGluZ01hc2NvdCB9IGZyb20gJy4uL2xpYi9pY29ucy5qcyc7XG5cbmNvbnNvbGUubG9nKCc8TG9naW4+IGhhcyBiZWVuIGxhenkgbG9hZGVkIScpO1xuXG5jb25zdCBBUElfRU5EUE9JTlQgPSBgJHtwcm9jZXNzLmVudi5BUElfRU5EUE9JTlR9L2xvZ2luYDtcblxuY29uc3QgaW5pdGlhbFN0YXRlID0ge1xuICAgIGlzT3BlbjogdHJ1ZSxcbiAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgIG1vZGU6ICdsb2dpbicsIC8vIGxvZ2luOyByZWdpc3RlclxuICAgIGN1cnJlbnQ6ICd1c2VybmFtZScsIC8vIEZvcm0gZmllbGQgdG8gZmlsbC4uLiB1c2VybmFtZSwgcGFzc3dvcmQsIGVtYWlsXG4gICAgdXNlcjoge30sIC8vIHVzZXIgY3JlZGVudGlhbHM6IHVzZXJuYW1lLCBlbWFpbCwgcGFzc3dvcmRcbiAgICBlcnJvcjoge30sXG59O1xuY29uc3QgcmVkdWNlciA9IChzdGF0ZSwgYWN0aW9uKSA9PiB7XG4gICAgc3dpdGNoIChhY3Rpb24udHlwZSkge1xuICAgICAgICBjYXNlICdUT0dHTEUnOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5pbml0aWFsU3RhdGUsXG4gICAgICAgICAgICAgICAgaXNPcGVuOiAhc3RhdGUuaXNPcGVuLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnSU5JVCc6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogdHJ1ZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ1NVQk1JVCc6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICAgICAgY3VycmVudDogYWN0aW9uLmN1cnJlbnQsXG4gICAgICAgICAgICAgICAgdXNlcjoge1xuICAgICAgICAgICAgICAgICAgICAuLi5zdGF0ZS51c2VyLFxuICAgICAgICAgICAgICAgICAgICBbYWN0aW9uLmlucHV0TmFtZV06IGFjdGlvbi5pbnB1dFZhbHVlLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdSRUdJU1RFUic6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICAgICAgbW9kZTogJ3JlZ2lzdGVyJyxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ0xPR0lOX1NVQ0NFU1MnOlxuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpO1xuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnTE9HSU5fRVJST1InOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IHRydWUsXG4gICAgICAgICAgICAgICAgZXJyb3I6IGFjdGlvbi5lcnJvciA/IGFjdGlvbi5lcnJvciA6IHsgbWVzc2FnZTogJ0FuIGVycm9yIG9jY3VycmVkLicgfSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoKTtcbiAgICB9XG59O1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBMb2dpbigpIHtcbiAgICBjb25zdCBmb3JtID0gUmVhY3QudXNlUmVmKCk7XG5cbiAgICBjb25zdCBbc3RhdGUsIGRpc3BhdGNoU3RhdGVdID0gUmVhY3QudXNlUmVkdWNlcihyZWR1Y2VyLCBpbml0aWFsU3RhdGUpO1xuXG4gICAgY29uc3QgdG9nZ2xlT3BlbiA9IChldmVudCkgPT4ge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ1RPR0dMRScgfSk7XG4gICAgfTtcblxuICAgIGNvbnN0IGhhbmRsZVN1Ym1pdCA9IGFzeW5jIChldmVudCkgPT4ge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnSU5JVCcgfSk7XG5cbiAgICAgICAgY29uc3QgaW5wdXQgPSBmb3JtWydjdXJyZW50J11bc3RhdGUuY3VycmVudF1bJ3ZhbHVlJ107XG5cbiAgICAgICAgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICd1c2VybmFtZScpIHtcbiAgICAgICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goYCR7QVBJX0VORFBPSU5UfS8ke2lucHV0fWAsIHtcbiAgICAgICAgICAgICAgICBtZXRob2Q6ICdHRVQnLFxuICAgICAgICAgICAgICAgIG1vZGU6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIGlmIChyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgIGNvbnN0IHJlc3VsdCA9IGF3YWl0IHJlc3BvbnNlLmpzb24oKTtcbiAgICAgICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHtcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogJ1NVQk1JVCcsXG4gICAgICAgICAgICAgICAgICAgIGlucHV0TmFtZTogJ3VzZXJuYW1lJyxcbiAgICAgICAgICAgICAgICAgICAgaW5wdXRWYWx1ZTogcmVzdWx0LmNvbGxlY3Rpb24uaXRlbXNbMF0udXNlcm5hbWUsXG4gICAgICAgICAgICAgICAgICAgIGN1cnJlbnQ6ICdwYXNzd29yZCcsXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnUkVHSVNURVInIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICdwYXNzd29yZCcpIHtcbiAgICAgICAgICAgIGNvbnN0IHBheWxvYWQgPSB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUudXNlcixcbiAgICAgICAgICAgICAgICBwYXNzd29yZDogaW5wdXQsXG4gICAgICAgICAgICB9O1xuXG4gICAgICAgICAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGZldGNoKEFQSV9FTkRQT0lOVCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ1BPU1QnLFxuICAgICAgICAgICAgICAgIG1vZGU6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgaGVhZGVyczoge1xuICAgICAgICAgICAgICAgICAgICAnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL2pzb24nLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgYm9keTogSlNPTi5zdHJpbmdpZnkocGF5bG9hZCksXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIGlmIChyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnTE9HSU5fU1VDQ0VTUycgfSk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGNvbnN0IHJlc3VsdCA9IGF3YWl0IHJlc3BvbnNlLmpzb24oKTtcbiAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhyZXNwb25zZSwgcmVzdWx0KTtcbiAgICAgICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ0xPR0lOX0VSUk9SJywgZXJyb3I6IHJlc3VsdC5jb2xsZWN0aW9uLmVycm9yc1swXSB9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnTE9HSU5fRVJST1InLCBlcnJvcjogJ0FuIHVua25vd24gZXJyb3Igb2NjdXJyZWQuJyB9KTtcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICBjb25zdCBMb2dpbkJ1dHRvbiA9ICgpID0+IChcbiAgICAgICAgPGEgaHJlZj1cIi9sb2dpbi5waHBcIiB0aXRsZT1cIkxvZ2luXCIgb25DbGljaz17dG9nZ2xlT3Blbn0gY2xhc3NOYW1lPVwidXNlciB1c2VyLXVua25vd25cIj5cbiAgICAgICAgICAgIDxRdWVzdGlvbkJsb2NrIGNsYXNzTmFtZT1cInVzZXItYXZhdGFyIHRodW1ibmFpbFwiIC8+XG4gICAgICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyLXVzZXJuYW1lXCI+TG9naW48L3NwYW4+XG4gICAgICAgIDwvYT5cbiAgICApO1xuXG4gICAgbGV0IG1lc3NhZ2U7XG4gICAgaWYgKHN0YXRlLmlzRXJyb3IpIHtcbiAgICAgICAgbWVzc2FnZSA9IDxkaXYgY2xhc3NOYW1lPVwiZXJyb3JcIj57c3RhdGUuZXJyb3IuaGFzT3duUHJvcGVydHkoJ21lc3NhZ2UnKSA/IHN0YXRlLmVycm9yLm1lc3NhZ2UgOiAnQW4gZXJyb3Igb2NjdXJyZWQnfTwvZGl2PjtcbiAgICB9IGVsc2UgaWYgKHN0YXRlLm1vZGUgPT09ICdsb2dpbicpIHtcbiAgICAgICAgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICd1c2VybmFtZScpIG1lc3NhZ2UgPSBcIkVybS4uLiBXaGF0J3MgeW91ciBuYW1lIGFnYWluP1wiO1xuICAgICAgICBlbHNlIGlmIChzdGF0ZS5jdXJyZW50ID09PSAncGFzc3dvcmQnKSBtZXNzYWdlID0gYFRoYXQncyByaWdodCEgSSByZW1lbWJlciBub3chIFlvdXIgbmFtZSBpcyAke3N0YXRlLnVzZXIudXNlcm5hbWV9IWA7XG4gICAgfSBlbHNlIGlmIChzdGF0ZS5tb2RlID09PSAncmVnaXN0ZXInKSB7XG4gICAgICAgIG1lc3NhZ2UgPSAnV2UgZG9uXFwndCBoYXZlIGFueW9uZSByZWdpc3RlcmVkIGJ5IHRoYXQgbmFtZS4gV291bGQgeW91IGxpa2UgdG8gcmVnaXN0ZXI/JztcbiAgICB9XG5cbiAgICBjb25zdCBMb2dpbkZvcm0gPSAoKSA9PiB7XG4gICAgICAgIGlmIChzdGF0ZS5jdXJyZW50ID09PSAndXNlcm5hbWUnKSB7XG4gICAgICAgICAgICByZXR1cm4gPFVuZGVybGluZWRJbnB1dCBuYW1lPVwidXNlcm5hbWVcIiBwbGFjZWhvbGRlcj1cIlVzZXJuYW1lIG9yIEVtYWlsXCIgcGFkZGluZz17MTl9IGF1dG9mb2N1cyAvPjtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiA8VW5kZXJsaW5lZElucHV0IHR5cGU9XCJwYXNzd29yZFwiIG5hbWU9XCJwYXNzd29yZFwiIHBsYWNlaG9sZGVyPVwiUGFzc3dvcmRcIiBwYWRkaW5nPXsxOX0gYXV0b2ZvY3VzIC8+O1xuICAgIH07XG5cbiAgICBjb25zdCBSZWdpc3RlckZvcm0gPSAoKSA9PiAnUmVnaXN0ZXIgZm9ybSBoZXJlLi4uLic7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8PlxuICAgICAgICAgICAgPExvZ2luQnV0dG9uIC8+XG4gICAgICAgICAgICA8TW9kYWwgb3Blbj17c3RhdGUuaXNPcGVufSBjbG9zZT17dG9nZ2xlT3Blbn0+XG4gICAgICAgICAgICAgICAgPGZvcm0gaWQ9XCJsb2dpbmZvcm1cIiByZWY9e2Zvcm19IG9uU3VibWl0PXtoYW5kbGVTdWJtaXR9IGNsYXNzTmFtZT17c3RhdGUuaXNMb2FkaW5nID8gJ2xvYWRpbmcnIDogdW5kZWZpbmVkfT5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1uYXZcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxOYXZNZW51PlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxOYXZNZW51Lkl0ZW0gc2VsZWN0ZWQ+PGEgaHJlZj1cIiNsb2dpblwiPk5ldyBOYW1lPC9hPjwvTmF2TWVudS5JdGVtPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxOYXZNZW51Lkl0ZW0+PGEgaHJlZj1cIlwiPkJsdWU8L2E+PC9OYXZNZW51Lkl0ZW0+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPE5hdk1lbnUuSXRlbT48YSBocmVmPVwiXCI+R2FyeTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCJcIj5Kb2huPC9hPjwvTmF2TWVudS5JdGVtPlxuICAgICAgICAgICAgICAgICAgICAgICAgPC9OYXZNZW51PlxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1yaXZhbFwiIC8+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tbWVzc2FnZVwiPlxuICAgICAgICAgICAgICAgICAgICAgICAge21lc3NhZ2V9XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLWlucHV0XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICB7c3RhdGUubW9kZSA9PT0gJ2xvZ2luJyA/IDxMb2dpbkZvcm0gLz4gOiA8UmVnaXN0ZXJGb3JtIC8+fVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1zdWJtaXRcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHtzdGF0ZS5pc0xvYWRpbmcgPyAoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPD5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPExvYWRpbmdNYXNjb3QgY2xhc3NOYW1lPVwibG9hZGluZ1wiIC8+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuPlByb2Zlc3NvciBPYWsgaXMgdGhpbmtpbmc8L3NwYW4+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC8+XG4gICAgICAgICAgICAgICAgICAgICAgICApIDogKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxidXR0b24gdHlwZT1cInN1Ym1pdFwiIGRpc2FibGVkPXtzdGF0ZS5pc0xvYWRpbmd9PlN1Ym1pdDwvYnV0dG9uPlxuICAgICAgICAgICAgICAgICAgICAgICAgKX1cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgPC9mb3JtPlxuICAgICAgICAgICAgPC9Nb2RhbD5cbiAgICAgICAgPC8+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmNvbnN0IHR5cG9ncmFwaHkgPSB7XG4gICAgZm9udEZhbWlseTogJ1ByZXNzIFN0YXJ0Jyxcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIFVuZGVybGluZWRJbnB1dCh7IHZhbHVlID0gJycsIHR5cGUgPSAndGV4dCcsIHBhZGRpbmcgPSAxMCwgYXV0b2ZvY3VzLCAuLi5wcm9wcyB9KSB7XG4gICAgY29uc3QgW3N0YXRlLCBzZXRTdGF0ZV0gPSBSZWFjdC51c2VTdGF0ZSh2YWx1ZSk7XG4gICAgY29uc3QgaGFuZGxlQ2hhbmdlID0gKGV2ZW50KSA9PiB7XG4gICAgICAgIHNldFN0YXRlKGV2ZW50LnRhcmdldC52YWx1ZSk7XG4gICAgfTtcbiAgICBjb25zdCBwbGFjZWhvbGRlclBhZCA9IHBhZGRpbmcgLSBzdGF0ZS5sZW5ndGg7XG4gICAgY29uc3QgcGxhY2Vob2xkZXIgPSAnXycucmVwZWF0KHBsYWNlaG9sZGVyUGFkID4gMCA/IHBsYWNlaG9sZGVyUGFkIDogMCk7XG5cbiAgICBpZiAoYXV0b2ZvY3VzKSB7XG4gICAgICAgIHByb3BzLnJlZiA9IChpbnB1dCkgPT4gaW5wdXQgJiYgaW5wdXQuZm9jdXMoKTtcbiAgICB9XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cInVuZGVybGluZWRpbnB1dFwiIHN0eWxlPXt7IHBvc2l0aW9uOiAncmVsYXRpdmUnIH19PlxuICAgICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJ1bmRlcmxpbmVcIiBzdHlsZT17eyBwb3NpdGlvbjogJ2Fic29sdXRlJywgbGVmdDogMCwgdG9wOiAnLjNlbScsIHpJbmRleDogMCwgLi4udHlwb2dyYXBoeSB9fT5cbiAgICAgICAgICAgICAgICA8c3BhbiBzdHlsZT17eyBvcGFjaXR5OiAwIH19PntzdGF0ZX08L3NwYW4+XG4gICAgICAgICAgICAgICAgPHNwYW4+e3BsYWNlaG9sZGVyfTwvc3Bhbj5cbiAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgPGlucHV0IHR5cGU9e3R5cGV9IHZhbHVlPXtzdGF0ZX0gb25DaGFuZ2U9e2hhbmRsZUNoYW5nZX0gc3R5bGU9e3sgcG9zaXRpb246ICdyZWxhdGl2ZScsIHpJbmRleDogMSwgLi4udHlwb2dyYXBoeSB9fSB7Li4ucHJvcHN9IC8+XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG4iXSwic291cmNlUm9vdCI6IiJ9