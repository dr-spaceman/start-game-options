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
/* harmony import */ var _ui_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ui/Modal.jsx */ "./browser/src/components/ui/Modal.jsx");
/* harmony import */ var _ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ui/UnderlinedInput.jsx */ "./browser/src/components/ui/UnderlinedInput.jsx");
/* harmony import */ var _ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ui/NavMenu.jsx */ "./browser/src/components/ui/NavMenu.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");
/* harmony import */ var _lib_icons_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../lib/icons.js */ "./browser/src/lib/icons.js");
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
    case 'RESET':
      return { ...initialState
      };

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

function Login({
  LoginButton
}) {
  const form = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();
  const [state, dispatchState] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useReducer(reducer, initialState);

  const toggleOpen = event => {
    event.preventDefault();
    dispatchState({
      type: 'TOGGLE'
    });
  };

  const resetForm = () => {
    dispatchState({
      type: 'RESET'
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
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
        name: "username",
        placeholder: "Username or Email",
        padding: 19,
        autofocus: true
      });
    }

    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
      type: "password",
      name: "password",
      placeholder: "Password",
      padding: 19,
      autofocus: true
    });
  };

  const RegisterForm = () => 'Register form here....';

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginButton, {
    handleClick: toggleOpen
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: state.isOpen,
    close: toggleOpen
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("form", {
    id: "loginform",
    ref: form,
    onSubmit: handleSubmit,
    className: state.isLoading ? 'loading' : undefined
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-nav"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, {
    selected: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#login",
    onClick: resetForm
  }, "New Name")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#blue",
    onClick: resetForm
  }, "Blue")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#gary",
    onClick: resetForm
  }, "Gary")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#john",
    onClick: resetForm
  }, "John")))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-rival"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-message"
  }, message), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-input"
  }, state.mode === 'login' ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginForm, null) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(RegisterForm, null)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-submit"
  }, state.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, "Oak is thinking")) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, state.mode === 'login' ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_4__["default"], {
    onClick: () => dispatchState({
      type: 'REGISTER'
    })
  }, "Register") : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_4__["default"], {
    onClick: resetForm
  }, "Login"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_4__["default"], {
    variant: "contained",
    type: "submit"
  }, "Submit"))))));
}

/***/ }),

/***/ "./browser/src/components/ui/UnderlinedInput.jsx":
/*!*******************************************************!*\
  !*** ./browser/src/components/ui/UnderlinedInput.jsx ***!
  \*******************************************************/
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0xvZ2luLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL3VpL1VuZGVybGluZWRJbnB1dC5qc3giXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsIkFQSV9FTkRQT0lOVCIsInByb2Nlc3MiLCJpbml0aWFsU3RhdGUiLCJpc09wZW4iLCJpc0xvYWRpbmciLCJpc0Vycm9yIiwibW9kZSIsImN1cnJlbnQiLCJ1c2VyIiwiZXJyb3IiLCJyZWR1Y2VyIiwic3RhdGUiLCJhY3Rpb24iLCJ0eXBlIiwiaW5wdXROYW1lIiwiaW5wdXRWYWx1ZSIsIndpbmRvdyIsImxvY2F0aW9uIiwicmVsb2FkIiwibWVzc2FnZSIsIkVycm9yIiwiTG9naW4iLCJMb2dpbkJ1dHRvbiIsImZvcm0iLCJSZWFjdCIsInVzZVJlZiIsImRpc3BhdGNoU3RhdGUiLCJ1c2VSZWR1Y2VyIiwidG9nZ2xlT3BlbiIsImV2ZW50IiwicHJldmVudERlZmF1bHQiLCJyZXNldEZvcm0iLCJoYW5kbGVTdWJtaXQiLCJpbnB1dCIsInJlc3BvbnNlIiwiZmV0Y2giLCJtZXRob2QiLCJjcmVkZW50aWFscyIsIm9rIiwicmVzdWx0IiwianNvbiIsImNvbGxlY3Rpb24iLCJpdGVtcyIsInVzZXJuYW1lIiwicGF5bG9hZCIsInBhc3N3b3JkIiwiaGVhZGVycyIsImJvZHkiLCJKU09OIiwic3RyaW5naWZ5IiwiZXJyb3JzIiwiaGFzT3duUHJvcGVydHkiLCJMb2dpbkZvcm0iLCJSZWdpc3RlckZvcm0iLCJ1bmRlZmluZWQiLCJ0eXBvZ3JhcGh5IiwiZm9udEZhbWlseSIsIlVuZGVybGluZWRJbnB1dCIsInZhbHVlIiwicGFkZGluZyIsImF1dG9mb2N1cyIsInByb3BzIiwic2V0U3RhdGUiLCJ1c2VTdGF0ZSIsImhhbmRsZUNoYW5nZSIsInRhcmdldCIsInBsYWNlaG9sZGVyUGFkIiwibGVuZ3RoIiwicGxhY2Vob2xkZXIi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OUIsQ0FESixlQUVJLHlFQUFPeUQsV0FBUCxDQUZKLENBREosZUFLSTtBQUFPLFFBQUksRUFBRXZELElBQWI7QUFBbUIsU0FBSyxFQUFFRixLQUExQjtBQUFpQyxZQUFRLEVBQUVxRCxZQUEzQztBQUF5RCxTQUFLLEVBQUU7QUFBRVEsY0FBUSxFQUFFLFVBQVo7QUFBd0JHLFlBQU0sRUFBRSxDQUFoQztBQUFtQyxTQUFHcEI7QUFBdEM7QUFBaEUsS0FBd0hNLEtBQXhILEVBTEosQ0FESjtBQVNILEMiLCJmaWxlIjoiTG9naW5fYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyogZXNsaW50LWRpc2FibGUgbm8tcHJvdG90eXBlLWJ1aWx0aW5zICovXG5cbi8qKlxuICogTG9naW4gY29tcG9uZW50IHdpdGggbG9naW4gZm9ybVxuICogTm90ZTogQ29tcG9uZW50IGlzIGxhenkgbG9hZGVkIHVwb24gY2xpY2tpbmcgbG9naW4gYnV0dG9uXG4gKi9cblxuaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBNb2RhbCBmcm9tICcuL3VpL01vZGFsLmpzeCc7XG5pbXBvcnQgVW5kZXJsaW5lZElucHV0IGZyb20gJy4vdWkvVW5kZXJsaW5lZElucHV0LmpzeCc7XG5pbXBvcnQgTmF2TWVudSBmcm9tICcuL3VpL05hdk1lbnUuanN4JztcbmltcG9ydCBCdXR0b24gZnJvbSAnLi91aS9CdXR0b24uanN4JztcbmltcG9ydCB7IFF1ZXN0aW9uQmxvY2ssIExvYWRpbmdNYXNjb3QgfSBmcm9tICcuLi9saWIvaWNvbnMuanMnO1xuXG5jb25zb2xlLmxvZygnPExvZ2luPiBoYXMgYmVlbiBsYXp5IGxvYWRlZCEnKTtcblxuY29uc3QgQVBJX0VORFBPSU5UID0gYCR7cHJvY2Vzcy5lbnYuQVBJX0VORFBPSU5UfS9sb2dpbmA7XG5cbmNvbnN0IGluaXRpYWxTdGF0ZSA9IHtcbiAgICBpc09wZW46IHRydWUsXG4gICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICBtb2RlOiAnbG9naW4nLCAvLyBsb2dpbjsgcmVnaXN0ZXJcbiAgICBjdXJyZW50OiAndXNlcm5hbWUnLCAvLyBGb3JtIGZpZWxkIHRvIGZpbGwuLi4gdXNlcm5hbWUsIHBhc3N3b3JkLCBlbWFpbFxuICAgIHVzZXI6IHt9LCAvLyB1c2VyIGNyZWRlbnRpYWxzOiB1c2VybmFtZSwgZW1haWwsIHBhc3N3b3JkXG4gICAgZXJyb3I6IHt9LFxufTtcbmNvbnN0IHJlZHVjZXIgPSAoc3RhdGUsIGFjdGlvbikgPT4ge1xuICAgIHN3aXRjaCAoYWN0aW9uLnR5cGUpIHtcbiAgICAgICAgY2FzZSAnUkVTRVQnOlxuICAgICAgICAgICAgcmV0dXJuIHsgLi4uaW5pdGlhbFN0YXRlIH07XG4gICAgICAgIGNhc2UgJ1RPR0dMRSc6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLmluaXRpYWxTdGF0ZSxcbiAgICAgICAgICAgICAgICBpc09wZW46ICFzdGF0ZS5pc09wZW4sXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdJTklUJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiB0cnVlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnU1VCTUlUJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBjdXJyZW50OiBhY3Rpb24uY3VycmVudCxcbiAgICAgICAgICAgICAgICB1c2VyOiB7XG4gICAgICAgICAgICAgICAgICAgIC4uLnN0YXRlLnVzZXIsXG4gICAgICAgICAgICAgICAgICAgIFthY3Rpb24uaW5wdXROYW1lXTogYWN0aW9uLmlucHV0VmFsdWUsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ1JFR0lTVEVSJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBtb2RlOiAncmVnaXN0ZXInLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnTE9HSU5fU1VDQ0VTUyc6XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24ucmVsb2FkKCk7XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdMT0dJTl9FUlJPUic6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogdHJ1ZSxcbiAgICAgICAgICAgICAgICBlcnJvcjogYWN0aW9uLmVycm9yID8gYWN0aW9uLmVycm9yIDogeyBtZXNzYWdlOiAnQW4gZXJyb3Igb2NjdXJyZWQuJyB9LFxuICAgICAgICAgICAgfTtcbiAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcigpO1xuICAgIH1cbn07XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIExvZ2luKHsgTG9naW5CdXR0b24gfSkge1xuICAgIGNvbnN0IGZvcm0gPSBSZWFjdC51c2VSZWYoKTtcblxuICAgIGNvbnN0IFtzdGF0ZSwgZGlzcGF0Y2hTdGF0ZV0gPSBSZWFjdC51c2VSZWR1Y2VyKHJlZHVjZXIsIGluaXRpYWxTdGF0ZSk7XG5cbiAgICBjb25zdCB0b2dnbGVPcGVuID0gKGV2ZW50KSA9PiB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnVE9HR0xFJyB9KTtcbiAgICB9O1xuXG4gICAgY29uc3QgcmVzZXRGb3JtID0gKCkgPT4ge1xuICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ1JFU0VUJyB9KTtcbiAgICB9O1xuXG4gICAgY29uc3QgaGFuZGxlU3VibWl0ID0gYXN5bmMgKGV2ZW50KSA9PiB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdJTklUJyB9KTtcblxuICAgICAgICBjb25zdCBpbnB1dCA9IGZvcm1bJ2N1cnJlbnQnXVtzdGF0ZS5jdXJyZW50XVsndmFsdWUnXTtcblxuICAgICAgICBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3VzZXJuYW1lJykge1xuICAgICAgICAgICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBmZXRjaChgJHtBUElfRU5EUE9JTlR9LyR7aW5wdXR9YCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ0dFVCcsXG4gICAgICAgICAgICAgICAgbW9kZTogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoe1xuICAgICAgICAgICAgICAgICAgICB0eXBlOiAnU1VCTUlUJyxcbiAgICAgICAgICAgICAgICAgICAgaW5wdXROYW1lOiAndXNlcm5hbWUnLFxuICAgICAgICAgICAgICAgICAgICBpbnB1dFZhbHVlOiByZXN1bHQuY29sbGVjdGlvbi5pdGVtc1swXS51c2VybmFtZSxcbiAgICAgICAgICAgICAgICAgICAgY3VycmVudDogJ3Bhc3N3b3JkJyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdSRUdJU1RFUicgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0gZWxzZSBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3Bhc3N3b3JkJykge1xuICAgICAgICAgICAgY29uc3QgcGF5bG9hZCA9IHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZS51c2VyLFxuICAgICAgICAgICAgICAgIHBhc3N3b3JkOiBpbnB1dCxcbiAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goQVBJX0VORFBPSU5ULCB7XG4gICAgICAgICAgICAgICAgbWV0aG9kOiAnUE9TVCcsXG4gICAgICAgICAgICAgICAgbW9kZTogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgICAgICdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24vanNvbicsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBib2R5OiBKU09OLnN0cmluZ2lmeShwYXlsb2FkKSxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdMT0dJTl9TVUNDRVNTJyB9KTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKHJlc3BvbnNlLCByZXN1bHQpO1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnTE9HSU5fRVJST1InLCBlcnJvcjogcmVzdWx0LmNvbGxlY3Rpb24uZXJyb3JzWzBdIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdMT0dJTl9FUlJPUicsIGVycm9yOiAnQW4gdW5rbm93biBlcnJvciBvY2N1cnJlZC4nIH0pO1xuICAgICAgICB9XG4gICAgfTtcblxuICAgIGxldCBtZXNzYWdlO1xuICAgIGlmIChzdGF0ZS5pc0Vycm9yKSB7XG4gICAgICAgIG1lc3NhZ2UgPSA8ZGl2IGNsYXNzTmFtZT1cImVycm9yXCI+e3N0YXRlLmVycm9yLmhhc093blByb3BlcnR5KCdtZXNzYWdlJykgPyBzdGF0ZS5lcnJvci5tZXNzYWdlIDogJ0FuIGVycm9yIG9jY3VycmVkJ308L2Rpdj47XG4gICAgfSBlbHNlIGlmIChzdGF0ZS5tb2RlID09PSAnbG9naW4nKSB7XG4gICAgICAgIGlmIChzdGF0ZS5jdXJyZW50ID09PSAndXNlcm5hbWUnKSBtZXNzYWdlID0gXCJFcm0uLi4gV2hhdCdzIHlvdXIgbmFtZSBhZ2Fpbj9cIjtcbiAgICAgICAgZWxzZSBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3Bhc3N3b3JkJykgbWVzc2FnZSA9IGBUaGF0J3MgcmlnaHQhIEkgcmVtZW1iZXIgbm93ISBZb3VyIG5hbWUgaXMgJHtzdGF0ZS51c2VyLnVzZXJuYW1lfSFgO1xuICAgIH0gZWxzZSBpZiAoc3RhdGUubW9kZSA9PT0gJ3JlZ2lzdGVyJykge1xuICAgICAgICBtZXNzYWdlID0gJ1dlIGRvblxcJ3QgaGF2ZSBhbnlvbmUgcmVnaXN0ZXJlZCBieSB0aGF0IG5hbWUuIFdvdWxkIHlvdSBsaWtlIHRvIHJlZ2lzdGVyPyc7XG4gICAgfVxuXG4gICAgY29uc3QgTG9naW5Gb3JtID0gKCkgPT4ge1xuICAgICAgICBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3VzZXJuYW1lJykge1xuICAgICAgICAgICAgcmV0dXJuIDxVbmRlcmxpbmVkSW5wdXQgbmFtZT1cInVzZXJuYW1lXCIgcGxhY2Vob2xkZXI9XCJVc2VybmFtZSBvciBFbWFpbFwiIHBhZGRpbmc9ezE5fSBhdXRvZm9jdXMgLz47XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gPFVuZGVybGluZWRJbnB1dCB0eXBlPVwicGFzc3dvcmRcIiBuYW1lPVwicGFzc3dvcmRcIiBwbGFjZWhvbGRlcj1cIlBhc3N3b3JkXCIgcGFkZGluZz17MTl9IGF1dG9mb2N1cyAvPjtcbiAgICB9O1xuXG4gICAgY29uc3QgUmVnaXN0ZXJGb3JtID0gKCkgPT4gJ1JlZ2lzdGVyIGZvcm0gaGVyZS4uLi4nO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPD5cbiAgICAgICAgICAgIDxMb2dpbkJ1dHRvbiBoYW5kbGVDbGljaz17dG9nZ2xlT3Blbn0gLz5cbiAgICAgICAgICAgIDxNb2RhbCBvcGVuPXtzdGF0ZS5pc09wZW59IGNsb3NlPXt0b2dnbGVPcGVufT5cbiAgICAgICAgICAgICAgICA8Zm9ybSBpZD1cImxvZ2luZm9ybVwiIHJlZj17Zm9ybX0gb25TdWJtaXQ9e2hhbmRsZVN1Ym1pdH0gY2xhc3NOYW1lPXtzdGF0ZS5pc0xvYWRpbmcgPyAnbG9hZGluZycgOiB1bmRlZmluZWR9PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLW5hdlwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPE5hdk1lbnU+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPE5hdk1lbnUuSXRlbSBzZWxlY3RlZD48YSBocmVmPVwiI2xvZ2luXCIgb25DbGljaz17cmVzZXRGb3JtfT5OZXcgTmFtZTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjYmx1ZVwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+Qmx1ZTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjZ2FyeVwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+R2FyeTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjam9oblwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+Sm9objwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgIDwvTmF2TWVudT5cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tcml2YWxcIiAvPlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLW1lc3NhZ2VcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHttZXNzYWdlfVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1pbnB1dFwiPlxuICAgICAgICAgICAgICAgICAgICAgICAge3N0YXRlLm1vZGUgPT09ICdsb2dpbicgPyA8TG9naW5Gb3JtIC8+IDogPFJlZ2lzdGVyRm9ybSAvPn1cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tc3VibWl0XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICB7c3RhdGUuaXNMb2FkaW5nID8gKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDw+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuPk9hayBpcyB0aGlua2luZzwvc3Bhbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICAgICAgICAgICkgOiAoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPD5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge3N0YXRlLm1vZGUgPT09ICdsb2dpbidcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgID8gPEJ1dHRvbiBvbkNsaWNrPXsoKSA9PiBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ1JFR0lTVEVSJyB9KX0+UmVnaXN0ZXI8L0J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogPEJ1dHRvbiBvbkNsaWNrPXtyZXNldEZvcm19PkxvZ2luPC9CdXR0b24+fVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8QnV0dG9uIHZhcmlhbnQ9XCJjb250YWluZWRcIiB0eXBlPVwic3VibWl0XCI+U3VibWl0PC9CdXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC8+XG4gICAgICAgICAgICAgICAgICAgICAgICApfVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICA8L2Zvcm0+XG4gICAgICAgICAgICA8L01vZGFsPlxuICAgICAgICA8Lz5cbiAgICApO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuY29uc3QgdHlwb2dyYXBoeSA9IHtcbiAgICBmb250RmFtaWx5OiAnUHJlc3MgU3RhcnQnLFxufTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVW5kZXJsaW5lZElucHV0KHsgdmFsdWUgPSAnJywgdHlwZSA9ICd0ZXh0JywgcGFkZGluZyA9IDEwLCBhdXRvZm9jdXMsIC4uLnByb3BzIH0pIHtcbiAgICBjb25zdCBbc3RhdGUsIHNldFN0YXRlXSA9IFJlYWN0LnVzZVN0YXRlKHZhbHVlKTtcbiAgICBjb25zdCBoYW5kbGVDaGFuZ2UgPSAoZXZlbnQpID0+IHtcbiAgICAgICAgc2V0U3RhdGUoZXZlbnQudGFyZ2V0LnZhbHVlKTtcbiAgICB9O1xuICAgIGNvbnN0IHBsYWNlaG9sZGVyUGFkID0gcGFkZGluZyAtIHN0YXRlLmxlbmd0aDtcbiAgICBjb25zdCBwbGFjZWhvbGRlciA9ICdfJy5yZXBlYXQocGxhY2Vob2xkZXJQYWQgPiAwID8gcGxhY2Vob2xkZXJQYWQgOiAwKTtcblxuICAgIGlmIChhdXRvZm9jdXMpIHtcbiAgICAgICAgcHJvcHMucmVmID0gKGlucHV0KSA9PiBpbnB1dCAmJiBpbnB1dC5mb2N1cygpO1xuICAgIH1cblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0XCIgc3R5bGU9e3sgcG9zaXRpb246ICdyZWxhdGl2ZScgfX0+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cInVuZGVybGluZVwiIHN0eWxlPXt7IHBvc2l0aW9uOiAnYWJzb2x1dGUnLCBsZWZ0OiAwLCB0b3A6ICcuM2VtJywgekluZGV4OiAwLCAuLi50eXBvZ3JhcGh5IH19PlxuICAgICAgICAgICAgICAgIDxzcGFuIHN0eWxlPXt7IG9wYWNpdHk6IDAgfX0+e3N0YXRlfTwvc3Bhbj5cbiAgICAgICAgICAgICAgICA8c3Bhbj57cGxhY2Vob2xkZXJ9PC9zcGFuPlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICA8aW5wdXQgdHlwZT17dHlwZX0gdmFsdWU9e3N0YXRlfSBvbkNoYW5nZT17aGFuZGxlQ2hhbmdlfSBzdHlsZT17eyBwb3NpdGlvbjogJ3JlbGF0aXZlJywgekluZGV4OiAxLCAuLi50eXBvZ3JhcGh5IH19IHsuLi5wcm9wc30gLz5cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=