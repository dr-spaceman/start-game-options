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
/* harmony import */ var _lib_icons_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../lib/icons.js */ "./browser/src/lib/icons.js");
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
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_3__["QuestionBlock"], {
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
        padding: 17,
        autofocus: true
      });
    }

    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
      type: "password",
      name: "password",
      placeholder: "Password",
      padding: 17,
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
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", {
    className: "navmenu"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "selected"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#login"
  }, "New Name")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "Blue")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "Gary")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: ""
  }, "John")))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-rival"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-message"
  }, message), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-input"
  }, state.mode === 'login' ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginForm, null) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(RegisterForm, null)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-submit"
  }, state.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_3__["LoadingMascot"], {
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0xvZ2luLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1VuZGVybGluZWRJbnB1dC5qc3giXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsIkFQSV9FTkRQT0lOVCIsInByb2Nlc3MiLCJpbml0aWFsU3RhdGUiLCJpc09wZW4iLCJpc0xvYWRpbmciLCJpc0Vycm9yIiwibW9kZSIsImN1cnJlbnQiLCJ1c2VyIiwiZXJyb3IiLCJyZWR1Y2VyIiwic3RhdGUiLCJhY3Rpb24iLCJ0eXBlIiwiaW5wdXROYW1lIiwiaW5wdXRWYWx1ZSIsIndpbmRvdyIsImxvY2F0aW9uIiwicmVsb2FkIiwibWVzc2FnZSIsIkVycm9yIiwiTG9naW4iLCJmb3JtIiwiUmVhY3QiLCJ1c2VSZWYiLCJkaXNwYXRjaFN0YXRlIiwidXNlUmVkdWNlciIsInRvZ2dsZU9wZW4iLCJldmVudCIsInByZXZlbnREZWZhdWx0IiwiaGFuZGxlU3VibWl0IiwiaW5wdXQiLCJyZXNwb25zZSIsImZldGNoIiwibWV0aG9kIiwiY3JlZGVudGlhbHMiLCJvayIsInJlc3VsdCIsImpzb24iLCJjb2xsZWN0aW9uIiwiaXRlbXMiLCJ1c2VybmFtZSIsInBheWxvYWQiLCJwYXNzd29yZCIsImhlYWRlcnMiLCJib2R5IiwiSlNPTiIsInN0cmluZ2lmeSIsImVycm9ycyIsIkxvZ2luQnV0dG9uIiwiaGFzT3duUHJvcGVydHkiLCJMb2dpbkZvcm0iLCJSZWdpc3RlckZvcm0iLCJ1bmRlZmluZWQiLCJ0eXBvZ3JhcGh5IiwiZm9udEZhbWlseSIsIlVuZGVybGluZWRJbnB1dCIsInZhbHVlIiwicGFkZGluZyIsImF1dG9mb2N1cyIsInByb3BzIiwic2V0U3RhdGUiLCJ1c2VTdGF0ZSIsImhhbmRsZUNoYW5nZSIsInRhcmdldCIsInBsYWNlaG9sZGVyUGFkIiwibGVuZ3RoIiwicGxhY2Vob2xkZXIiLCJyZXBlYXQiLCJyZWYi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LENBQUNqQyxLQUFLLENBQUNvQyxNQUFOLENBQWFQLEtBQWQsQ0FBUjtBQUNILEdBRkQ7O0FBR0EsUUFBTVEsY0FBYyxHQUFHUCxPQUFPLEdBQUcvQyxLQUFLLENBQUN1RCxNQUF2QztBQUNBLFFBQU1DLFdBQVcsR0FBRyxJQUFJQyxNQUFKLENBQVdILGNBQWMsR0FBRyxDQUFqQixHQUFxQkEsY0FBckIsR0FBc0MsQ0FBakQsQ0FBcEI7O0FBRUEsTUFBSU4sU0FBSixFQUFlO0FBQ1hDLFNBQUssQ0FBQ1MsR0FBTixHQUFhdEMsS0FBRCxJQUFXQSxLQUFLLElBQUlBLEtBQUssQ0FBQ3VDLEtBQU4sRUFBaEM7QUFDSDs7QUFFRCxzQkFDSTtBQUFLLGFBQVMsRUFBQyxpQkFBZjtBQUFpQyxTQUFLLEVBQUU7QUFBRUMsY0FBUSxFQUFFO0FBQVo7QUFBeEMsa0JBQ0k7QUFBSyxhQUFTLEVBQUMsV0FBZjtBQUEyQixTQUFLLEVBQUU7QUFBRUEsY0FBUSxFQUFFLFVBQVo7QUFBd0JDLFVBQUksRUFBRSxDQUE5QjtBQUFpQ0MsU0FBRyxFQUFFLE1BQXRDO0FBQThDQyxZQUFNLEVBQUUsQ0FBdEQ7QUFBeUQsU0FBR3BCO0FBQTVEO0FBQWxDLGtCQUNJO0FBQU0sU0FBSyxFQUFFO0FBQUVxQixhQUFPLEVBQUU7QUFBWDtBQUFiLEtBQThCaEUsS0FBOUIsQ0FESixlQUVJLHlFQUFPd0QsV0FBUCxDQUZKLENBREosZUFLSTtBQUFPLFFBQUksRUFBRXRELElBQWI7QUFBbUIsU0FBSyxFQUFFRixLQUExQjtBQUFpQyxZQUFRLEVBQUVvRCxZQUEzQztBQUF5RCxTQUFLLEVBQUU7QUFBRVEsY0FBUSxFQUFFLFVBQVo7QUFBd0JHLFlBQU0sRUFBRSxDQUFoQztBQUFtQyxTQUFHcEI7QUFBdEM7QUFBaEUsS0FBd0hNLEtBQXhILEVBTEosQ0FESjtBQVNILEMiLCJmaWxlIjoiTG9naW5fYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyogZXNsaW50LWRpc2FibGUgbm8tcHJvdG90eXBlLWJ1aWx0aW5zICovXG5cbi8qKlxuICogTG9naW4gY29tcG9uZW50IHdpdGggbG9naW4gZm9ybVxuICogTm90ZTogQ29tcG9uZW50IGlzIGxhenkgbG9hZGVkIHVwb24gY2xpY2tpbmcgbG9naW4gYnV0dG9uXG4gKi9cblxuaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBNb2RhbCBmcm9tICcuL01vZGFsLmpzeCc7XG5pbXBvcnQgVW5kZXJsaW5lZElucHV0IGZyb20gJy4vVW5kZXJsaW5lZElucHV0LmpzeCc7XG5pbXBvcnQgeyBRdWVzdGlvbkJsb2NrLCBMb2FkaW5nTWFzY290IH0gZnJvbSAnLi4vbGliL2ljb25zLmpzJztcblxuY29uc29sZS5sb2coJzxMb2dpbj4gaGFzIGJlZW4gbGF6eSBsb2FkZWQhJyk7XG5cbmNvbnN0IEFQSV9FTkRQT0lOVCA9IGAke3Byb2Nlc3MuZW52LkFQSV9FTkRQT0lOVH0vbG9naW5gO1xuXG5jb25zdCBpbml0aWFsU3RhdGUgPSB7XG4gICAgaXNPcGVuOiB0cnVlLFxuICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgaXNFcnJvcjogZmFsc2UsXG4gICAgbW9kZTogJ2xvZ2luJywgLy8gbG9naW47IHJlZ2lzdGVyXG4gICAgY3VycmVudDogJ3VzZXJuYW1lJywgLy8gRm9ybSBmaWVsZCB0byBmaWxsLi4uIHVzZXJuYW1lLCBwYXNzd29yZCwgZW1haWxcbiAgICB1c2VyOiB7fSwgLy8gdXNlciBjcmVkZW50aWFsczogdXNlcm5hbWUsIGVtYWlsLCBwYXNzd29yZFxuICAgIGVycm9yOiB7fSxcbn07XG5jb25zdCByZWR1Y2VyID0gKHN0YXRlLCBhY3Rpb24pID0+IHtcbiAgICBzd2l0Y2ggKGFjdGlvbi50eXBlKSB7XG4gICAgICAgIGNhc2UgJ1RPR0dMRSc6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLmluaXRpYWxTdGF0ZSxcbiAgICAgICAgICAgICAgICBpc09wZW46ICFzdGF0ZS5pc09wZW4sXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdJTklUJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiB0cnVlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnU1VCTUlUJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBjdXJyZW50OiBhY3Rpb24uY3VycmVudCxcbiAgICAgICAgICAgICAgICB1c2VyOiB7XG4gICAgICAgICAgICAgICAgICAgIC4uLnN0YXRlLnVzZXIsXG4gICAgICAgICAgICAgICAgICAgIFthY3Rpb24uaW5wdXROYW1lXTogYWN0aW9uLmlucHV0VmFsdWUsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ1JFR0lTVEVSJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBtb2RlOiAncmVnaXN0ZXInLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnTE9HSU5fU1VDQ0VTUyc6XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24ucmVsb2FkKCk7XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogZmFsc2UsXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdMT0dJTl9FUlJPUic6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgaXNFcnJvcjogdHJ1ZSxcbiAgICAgICAgICAgICAgICBlcnJvcjogYWN0aW9uLmVycm9yID8gYWN0aW9uLmVycm9yIDogeyBtZXNzYWdlOiAnQW4gZXJyb3Igb2NjdXJyZWQuJyB9LFxuICAgICAgICAgICAgfTtcbiAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcigpO1xuICAgIH1cbn07XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIExvZ2luKCkge1xuICAgIGNvbnN0IGZvcm0gPSBSZWFjdC51c2VSZWYoKTtcblxuICAgIGNvbnN0IFtzdGF0ZSwgZGlzcGF0Y2hTdGF0ZV0gPSBSZWFjdC51c2VSZWR1Y2VyKHJlZHVjZXIsIGluaXRpYWxTdGF0ZSk7XG5cbiAgICBjb25zdCB0b2dnbGVPcGVuID0gKGV2ZW50KSA9PiB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnVE9HR0xFJyB9KTtcbiAgICB9O1xuXG4gICAgY29uc3QgaGFuZGxlU3VibWl0ID0gYXN5bmMgKGV2ZW50KSA9PiB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdJTklUJyB9KTtcblxuICAgICAgICBjb25zdCBpbnB1dCA9IGZvcm1bJ2N1cnJlbnQnXVtzdGF0ZS5jdXJyZW50XVsndmFsdWUnXTtcblxuICAgICAgICBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3VzZXJuYW1lJykge1xuICAgICAgICAgICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBmZXRjaChgJHtBUElfRU5EUE9JTlR9LyR7aW5wdXR9YCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ0dFVCcsXG4gICAgICAgICAgICAgICAgbW9kZTogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoe1xuICAgICAgICAgICAgICAgICAgICB0eXBlOiAnU1VCTUlUJyxcbiAgICAgICAgICAgICAgICAgICAgaW5wdXROYW1lOiAndXNlcm5hbWUnLFxuICAgICAgICAgICAgICAgICAgICBpbnB1dFZhbHVlOiByZXN1bHQuY29sbGVjdGlvbi5pdGVtc1swXS51c2VybmFtZSxcbiAgICAgICAgICAgICAgICAgICAgY3VycmVudDogJ3Bhc3N3b3JkJyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdSRUdJU1RFUicgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0gZWxzZSBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3Bhc3N3b3JkJykge1xuICAgICAgICAgICAgY29uc3QgcGF5bG9hZCA9IHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZS51c2VyLFxuICAgICAgICAgICAgICAgIHBhc3N3b3JkOiBpbnB1dCxcbiAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goQVBJX0VORFBPSU5ULCB7XG4gICAgICAgICAgICAgICAgbWV0aG9kOiAnUE9TVCcsXG4gICAgICAgICAgICAgICAgbW9kZTogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgICAgICdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24vanNvbicsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBib2R5OiBKU09OLnN0cmluZ2lmeShwYXlsb2FkKSxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdMT0dJTl9TVUNDRVNTJyB9KTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKHJlc3BvbnNlLCByZXN1bHQpO1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnTE9HSU5fRVJST1InLCBlcnJvcjogcmVzdWx0LmNvbGxlY3Rpb24uZXJyb3JzWzBdIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdMT0dJTl9FUlJPUicsIGVycm9yOiAnQW4gdW5rbm93biBlcnJvciBvY2N1cnJlZC4nIH0pO1xuICAgICAgICB9XG4gICAgfTtcblxuICAgIGNvbnN0IExvZ2luQnV0dG9uID0gKCkgPT4gKFxuICAgICAgICA8YSBocmVmPVwiL2xvZ2luLnBocFwiIHRpdGxlPVwiTG9naW5cIiBvbkNsaWNrPXt0b2dnbGVPcGVufSBjbGFzc05hbWU9XCJ1c2VyIHVzZXItdW5rbm93blwiPlxuICAgICAgICAgICAgPFF1ZXN0aW9uQmxvY2sgY2xhc3NOYW1lPVwidXNlci1hdmF0YXIgdGh1bWJuYWlsXCIgLz5cbiAgICAgICAgICAgIDxzcGFuIGNsYXNzTmFtZT1cInVzZXItdXNlcm5hbWVcIj5Mb2dpbjwvc3Bhbj5cbiAgICAgICAgPC9hPlxuICAgICk7XG5cbiAgICBsZXQgbWVzc2FnZTtcbiAgICBpZiAoc3RhdGUuaXNFcnJvcikge1xuICAgICAgICBtZXNzYWdlID0gPGRpdiBjbGFzc05hbWU9XCJlcnJvclwiPntzdGF0ZS5lcnJvci5oYXNPd25Qcm9wZXJ0eSgnbWVzc2FnZScpID8gc3RhdGUuZXJyb3IubWVzc2FnZSA6ICdBbiBlcnJvciBvY2N1cnJlZCd9PC9kaXY+O1xuICAgIH0gZWxzZSBpZiAoc3RhdGUubW9kZSA9PT0gJ2xvZ2luJykge1xuICAgICAgICBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3VzZXJuYW1lJykgbWVzc2FnZSA9IFwiRXJtLi4uIFdoYXQncyB5b3VyIG5hbWUgYWdhaW4/XCI7XG4gICAgICAgIGVsc2UgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICdwYXNzd29yZCcpIG1lc3NhZ2UgPSBgVGhhdCdzIHJpZ2h0ISBJIHJlbWVtYmVyIG5vdyEgWW91ciBuYW1lIGlzICR7c3RhdGUudXNlci51c2VybmFtZX0hYDtcbiAgICB9IGVsc2UgaWYgKHN0YXRlLm1vZGUgPT09ICdyZWdpc3RlcicpIHtcbiAgICAgICAgbWVzc2FnZSA9ICdXZSBkb25cXCd0IGhhdmUgYW55b25lIHJlZ2lzdGVyZWQgYnkgdGhhdCBuYW1lLiBXb3VsZCB5b3UgbGlrZSB0byByZWdpc3Rlcj8nO1xuICAgIH1cblxuICAgIGNvbnN0IExvZ2luRm9ybSA9ICgpID0+IHtcbiAgICAgICAgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICd1c2VybmFtZScpIHtcbiAgICAgICAgICAgIHJldHVybiA8VW5kZXJsaW5lZElucHV0IG5hbWU9XCJ1c2VybmFtZVwiIHBsYWNlaG9sZGVyPVwiVXNlcm5hbWUgb3IgRW1haWxcIiBwYWRkaW5nPXsxN30gYXV0b2ZvY3VzIC8+O1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIDxVbmRlcmxpbmVkSW5wdXQgdHlwZT1cInBhc3N3b3JkXCIgbmFtZT1cInBhc3N3b3JkXCIgcGxhY2Vob2xkZXI9XCJQYXNzd29yZFwiIHBhZGRpbmc9ezE3fSBhdXRvZm9jdXMgLz47XG4gICAgfTtcblxuICAgIGNvbnN0IFJlZ2lzdGVyRm9ybSA9ICgpID0+ICdSZWdpc3RlciBmb3JtIGhlcmUuLi4uJztcblxuICAgIHJldHVybiAoXG4gICAgICAgIDw+XG4gICAgICAgICAgICA8TG9naW5CdXR0b24gLz5cbiAgICAgICAgICAgIDxNb2RhbCBvcGVuPXtzdGF0ZS5pc09wZW59IGNsb3NlPXt0b2dnbGVPcGVufT5cbiAgICAgICAgICAgICAgICA8Zm9ybSBpZD1cImxvZ2luZm9ybVwiIHJlZj17Zm9ybX0gb25TdWJtaXQ9e2hhbmRsZVN1Ym1pdH0gY2xhc3NOYW1lPXtzdGF0ZS5pc0xvYWRpbmcgPyAnbG9hZGluZycgOiB1bmRlZmluZWR9PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLW5hdlwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPHVsIGNsYXNzTmFtZT1cIm5hdm1lbnVcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGkgY2xhc3NOYW1lPVwic2VsZWN0ZWRcIj48YSBocmVmPVwiI2xvZ2luXCI+TmV3IE5hbWU8L2E+PC9saT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGk+PGEgaHJlZj1cIlwiPkJsdWU8L2E+PC9saT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGk+PGEgaHJlZj1cIlwiPkdhcnk8L2E+PC9saT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGk+PGEgaHJlZj1cIlwiPkpvaG48L2E+PC9saT5cbiAgICAgICAgICAgICAgICAgICAgICAgIDwvdWw+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLXJpdmFsXCIgLz5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1tZXNzYWdlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICB7bWVzc2FnZX1cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0taW5wdXRcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHtzdGF0ZS5tb2RlID09PSAnbG9naW4nID8gPExvZ2luRm9ybSAvPiA6IDxSZWdpc3RlckZvcm0gLz59XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLXN1Ym1pdFwiPlxuICAgICAgICAgICAgICAgICAgICAgICAge3N0YXRlLmlzTG9hZGluZyA/IChcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8PlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TG9hZGluZ01hc2NvdCBjbGFzc05hbWU9XCJsb2FkaW5nXCIgLz5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4+UHJvZmVzc29yIE9hayBpcyB0aGlua2luZzwvc3Bhbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICAgICAgICAgICkgOiAoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwic3VibWl0XCIgZGlzYWJsZWQ9e3N0YXRlLmlzTG9hZGluZ30+U3VibWl0PC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICApfVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICA8L2Zvcm0+XG4gICAgICAgICAgICA8L01vZGFsPlxuICAgICAgICA8Lz5cbiAgICApO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuY29uc3QgdHlwb2dyYXBoeSA9IHtcbiAgICBmb250RmFtaWx5OiAnUHJlc3MgU3RhcnQnLFxufTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVW5kZXJsaW5lZElucHV0KHsgdmFsdWUgPSAnJywgdHlwZSA9ICd0ZXh0JywgcGFkZGluZyA9IDEwLCBhdXRvZm9jdXMsIC4uLnByb3BzIH0pIHtcbiAgICBjb25zdCBbc3RhdGUsIHNldFN0YXRlXSA9IFJlYWN0LnVzZVN0YXRlKHZhbHVlKTtcbiAgICBjb25zdCBoYW5kbGVDaGFuZ2UgPSAoZXZlbnQpID0+IHtcbiAgICAgICAgc2V0U3RhdGUoZXZlbnQudGFyZ2V0LnZhbHVlKTtcbiAgICB9O1xuICAgIGNvbnN0IHBsYWNlaG9sZGVyUGFkID0gcGFkZGluZyAtIHN0YXRlLmxlbmd0aDtcbiAgICBjb25zdCBwbGFjZWhvbGRlciA9ICdfJy5yZXBlYXQocGxhY2Vob2xkZXJQYWQgPiAwID8gcGxhY2Vob2xkZXJQYWQgOiAwKTtcblxuICAgIGlmIChhdXRvZm9jdXMpIHtcbiAgICAgICAgcHJvcHMucmVmID0gKGlucHV0KSA9PiBpbnB1dCAmJiBpbnB1dC5mb2N1cygpO1xuICAgIH1cblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0XCIgc3R5bGU9e3sgcG9zaXRpb246ICdyZWxhdGl2ZScgfX0+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cInVuZGVybGluZVwiIHN0eWxlPXt7IHBvc2l0aW9uOiAnYWJzb2x1dGUnLCBsZWZ0OiAwLCB0b3A6ICcuM2VtJywgekluZGV4OiAwLCAuLi50eXBvZ3JhcGh5IH19PlxuICAgICAgICAgICAgICAgIDxzcGFuIHN0eWxlPXt7IG9wYWNpdHk6IDAgfX0+e3N0YXRlfTwvc3Bhbj5cbiAgICAgICAgICAgICAgICA8c3Bhbj57cGxhY2Vob2xkZXJ9PC9zcGFuPlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICA8aW5wdXQgdHlwZT17dHlwZX0gdmFsdWU9e3N0YXRlfSBvbkNoYW5nZT17aGFuZGxlQ2hhbmdlfSBzdHlsZT17eyBwb3NpdGlvbjogJ3JlbGF0aXZlJywgekluZGV4OiAxLCAuLi50eXBvZ3JhcGh5IH19IHsuLi5wcm9wc30gLz5cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=