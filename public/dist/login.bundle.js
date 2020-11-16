(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["login"],{

/***/ "./browser/src/components/layout/Login.jsx":
/*!*************************************************!*\
  !*** ./browser/src/components/layout/Login.jsx ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Login; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _ui_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ui/Modal.jsx */ "./browser/src/components/ui/Modal.jsx");
/* harmony import */ var _ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui/UnderlinedInput.jsx */ "./browser/src/components/ui/UnderlinedInput.jsx");
/* harmony import */ var _ui_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui/NavMenu.jsx */ "./browser/src/components/ui/NavMenu.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");
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
    // Form submit

    case 'SUBMIT':
      return { ...state,
        isLoading: true,
        isError: false,
        user: { ...state.user,
          [action.inputName]: action.inputValue
        }
      };
    // Submit result success

    case 'NEXT':
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

    case 'ERROR':
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
    const input = form['current'][state.current]['value'];
    dispatchState({
      type: 'SUBMIT',
      inputName: state.current === 'username' && input.includes('@') ? 'email' : state.current,
      inputValue: input,
      current: 'password'
    });

    if (state.current === 'username') {
      const response = await fetch(`${API_ENDPOINT}/${input}`, {
        method: 'GET',
        mode: 'same-origin',
        credentials: 'same-origin'
      });

      if (response.ok) {
        const result = await response.json();
        dispatchState({
          type: 'NEXT',
          inputName: 'username',
          inputValue: result.collection.items[0].username,
          current: 'password'
        });
      } else {
        // Username or email not found; Offer registration
        dispatchState({
          type: 'REGISTER'
        });
        dispatchState({
          type: 'ERROR',
          error: {
            message: 'We don\'t have anyone registered by that name. Would you like to register?'
          }
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
          type: 'ERROR',
          error: result.collection.errors[0]
        });
      }
    } else {
      dispatchState({
        type: 'ERROR',
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
  } else if (state.mode === 'register') {// message = 'Register here';
  }

  const LoginForm = () => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-login"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    name: "username",
    placeholder: "Username or Email",
    padding: 19,
    autofocus: state.current === 'username',
    hidden: state.current !== 'username'
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    type: "password",
    name: "password",
    placeholder: "Password",
    padding: 19,
    autofocus: state.current === 'password',
    hidden: state.current !== 'password'
  }));

  const RegisterForm = () => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "loginform-register"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    name: "username",
    value: state.user.username,
    placeholder: "Username",
    padding: 19,
    autofocus: true
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    name: "email",
    value: state.user.email,
    placeholder: "Email",
    padding: 19
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_UnderlinedInput_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    type: "password",
    name: "password",
    placeholder: "Password",
    padding: 19
  }));

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

/***/ "./browser/src/components/ui/UnderlinedInput.css":
/*!*******************************************************!*\
  !*** ./browser/src/components/ui/UnderlinedInput.css ***!
  \*******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
/* harmony import */ var _UnderlinedInput_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UnderlinedInput.css */ "./browser/src/components/ui/UnderlinedInput.css");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



function UnderlinedInput({
  name,
  value: initialValue = '',
  type = 'text',
  padding = 10,
  autofocus,
  placeholder: placeholderValue,
  ...props
}) {
  const [value, setValue] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(initialValue);

  const handleChange = event => {
    setValue(event.target.value);
  };

  const placeholderPad = padding - value.length;
  const placeholder = '_'.repeat(placeholderPad > 0 ? placeholderPad : 0);
  const viewValue = type === 'password' ? '*'.repeat(value.length) : value;

  if (autofocus) {
    props.ref = input => input && input.focus();
  }

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    className: "underlinedinput"
  }, props), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: type,
    name: name,
    value: value,
    onChange: handleChange,
    className: "underlinedinput-typography"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "underlinedinput-view"
  }, value ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "underlinedinput-value"
  }, viewValue), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "underlinedinput-carat"
  }, "\xA0")) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "underlinedinput-carat"
  }, "\xA0"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "underlinedinput-placeholder"
  }, placeholderValue))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "underlinedinput-underline"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    style: {
      opacity: 0
    }
  }, viewValue), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, placeholder)));
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL2xheW91dC9Mb2dpbi5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy91aS9VbmRlcmxpbmVkSW5wdXQuY3NzIiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvdWkvVW5kZXJsaW5lZElucHV0LmpzeCJdLCJuYW1lcyI6WyJjb25zb2xlIiwibG9nIiwiQVBJX0VORFBPSU5UIiwicHJvY2VzcyIsImluaXRpYWxTdGF0ZSIsImlzT3BlbiIsImlzTG9hZGluZyIsImlzRXJyb3IiLCJtb2RlIiwiY3VycmVudCIsInVzZXIiLCJlcnJvciIsInJlZHVjZXIiLCJzdGF0ZSIsImFjdGlvbiIsInR5cGUiLCJpbnB1dE5hbWUiLCJpbnB1dFZhbHVlIiwid2luZG93IiwibG9jYXRpb24iLCJyZWxvYWQiLCJtZXNzYWdlIiwiRXJyb3IiLCJMb2dpbiIsIkxvZ2luQnV0dG9uIiwiZm9ybSIsIlJlYWN0IiwidXNlUmVmIiwiZGlzcGF0Y2hTdGF0ZSIsInVzZVJlZHVjZXIiLCJ0b2dnbGVPcGVuIiwiZXZlbnQiLCJwcmV2ZW50RGVmYXVsdCIsInJlc2V0Rm9ybSIsImhhbmRsZVN1Ym1pdCIsImlucHV0IiwiaW5jbHVkZXMiLCJyZXNwb25zZSIsImZldGNoIiwibWV0aG9kIiwiY3JlZGVudGlhbHMiLCJvayIsInJlc3VsdCIsImpzb24iLCJjb2xsZWN0aW9uIiwiaXRlbXMiLCJ1c2VybmFtZSIsInBheWxvYWQiLCJwYXNzd29yZCIsImhlYWRlcnMiLCJib2R5IiwiSlNPTiIsInN0cmluZ2lmeSIsImVycm9ycyIsImhhc093blByb3BlcnR5IiwiTG9naW5Gb3JtIiwiUmVnaXN0ZXJGb3JtIiwiZW1haWwiLCJ1bmRlZmluZWQiLCJVbmRlcmxpbmVkSW5wdXQiLCJuYW1lIiwidmFsdWUiLCJpbml0aWFsVmFsdWUiLCJwYWRkaW5nIiwiYXV0b2ZvY3VzIiwicGxhY2Vob2xkZXIiLCJwbGFjZWhvbGRlclZhbHVlIiwicHJvcHMiLCJzZXRWYWx1ZSIsInVzZVN0YXRlIiwiaGFuZGxlQ2hhbmdlIiwidGFyZ2V0IiwicGxhY2Vob2xkZXJQYWQiLCJsZW5ndGgiLCJyZXBlYXQiLCJ2aWV3VmFsdWUiLCJyZWYiLCJmb2N1cyIsIm9wYWNpdHkiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7O0FBRUE7Ozs7QUFLQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUFBLE9BQU8sQ0FBQ0MsR0FBUixDQUFZLCtCQUFaO0FBRUEsTUFBTUMsWUFBWSxHQUFJLEdBQUVDLE1BQXlCLFFBQWpEO0FBRUEsTUFBTUMsWUFBWSxHQUFHO0FBQ2pCQyxRQUFNLEVBQUUsSUFEUztBQUVqQkMsV0FBUyxFQUFFLEtBRk07QUFHakJDLFNBQU8sRUFBRSxLQUhRO0FBSWpCQyxNQUFJLEVBQUUsT0FKVztBQUlGO0FBQ2ZDLFNBQU8sRUFBRSxVQUxRO0FBS0k7QUFDckJDLE1BQUksRUFBRSxFQU5XO0FBTVA7QUFDVkMsT0FBSyxFQUFFO0FBUFUsQ0FBckI7O0FBU0EsTUFBTUMsT0FBTyxHQUFHLENBQUNDLEtBQUQsRUFBUUMsTUFBUixLQUFtQjtBQUMvQixVQUFRQSxNQUFNLENBQUNDLElBQWY7QUFDSSxTQUFLLE9BQUw7QUFDSSxhQUFPLEVBQUUsR0FBR1g7QUFBTCxPQUFQOztBQUNKLFNBQUssUUFBTDtBQUNJLGFBQU8sRUFDSCxHQUFHQSxZQURBO0FBRUhDLGNBQU0sRUFBRSxDQUFDUSxLQUFLLENBQUNSO0FBRlosT0FBUDtBQUlKOztBQUNBLFNBQUssUUFBTDtBQUNJLGFBQU8sRUFDSCxHQUFHUSxLQURBO0FBRUhQLGlCQUFTLEVBQUUsSUFGUjtBQUdIQyxlQUFPLEVBQUUsS0FITjtBQUlIRyxZQUFJLEVBQUUsRUFDRixHQUFHRyxLQUFLLENBQUNILElBRFA7QUFFRixXQUFDSSxNQUFNLENBQUNFLFNBQVIsR0FBb0JGLE1BQU0sQ0FB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QUFRLEVBQUVTLFlBQXZEO0FBQXFFLGFBQVMsRUFBQztBQUEvRSxJQURKLGVBRUk7QUFBSyxhQUFTLEVBQUM7QUFBZixLQUNLVCxLQUFLLGdCQUNGLHFJQUNJO0FBQU0sYUFBUyxFQUFDO0FBQWhCLEtBQXlDYyxTQUF6QyxDQURKLGVBRUk7QUFBTSxhQUFTLEVBQUM7QUFBaEIsWUFGSixDQURFLGdCQU1GLHFJQUNJO0FBQU0sYUFBUyxFQUFDO0FBQWhCLFlBREosZUFFSTtBQUFNLGFBQVMsRUFBQztBQUFoQixLQUErQ1QsZ0JBQS9DLENBRkosQ0FQUixDQUZKLGVBZUk7QUFBSyxhQUFTLEVBQUM7QUFBZixrQkFDSTtBQUFNLFNBQUssRUFBRTtBQUFFWSxhQUFPLEVBQUU7QUFBWDtBQUFiLEtBQThCSCxTQUE5QixDQURKLGVBRUkseUVBQU9WLFdBQVAsQ0FGSixDQWZKLENBREo7QUFzQkgsQyIsImZpbGUiOiJsb2dpbi5idW5kbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiBlc2xpbnQtZGlzYWJsZSBuby1wcm90b3R5cGUtYnVpbHRpbnMgKi9cblxuLyoqXG4gKiBMb2dpbiBjb21wb25lbnQgd2l0aCBsb2dpbiBmb3JtXG4gKiBOb3RlOiBDb21wb25lbnQgaXMgbGF6eSBsb2FkZWQgdXBvbiBjbGlja2luZyBsb2dpbiBidXR0b25cbiAqL1xuXG5pbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IE1vZGFsIGZyb20gJy4uL3VpL01vZGFsLmpzeCc7XG5pbXBvcnQgVW5kZXJsaW5lZElucHV0IGZyb20gJy4uL3VpL1VuZGVybGluZWRJbnB1dC5qc3gnO1xuaW1wb3J0IE5hdk1lbnUgZnJvbSAnLi4vdWkvTmF2TWVudS5qc3gnO1xuaW1wb3J0IEJ1dHRvbiBmcm9tICcuLi91aS9CdXR0b24uanN4JztcblxuY29uc29sZS5sb2coJzxMb2dpbj4gaGFzIGJlZW4gbGF6eSBsb2FkZWQhJyk7XG5cbmNvbnN0IEFQSV9FTkRQT0lOVCA9IGAke3Byb2Nlc3MuZW52LkFQSV9FTkRQT0lOVH0vbG9naW5gO1xuXG5jb25zdCBpbml0aWFsU3RhdGUgPSB7XG4gICAgaXNPcGVuOiB0cnVlLFxuICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgaXNFcnJvcjogZmFsc2UsXG4gICAgbW9kZTogJ2xvZ2luJywgLy8gbG9naW47IHJlZ2lzdGVyXG4gICAgY3VycmVudDogJ3VzZXJuYW1lJywgLy8gRm9ybSBmaWVsZCB0byBmaWxsLi4uIHVzZXJuYW1lLCBwYXNzd29yZCwgZW1haWxcbiAgICB1c2VyOiB7fSwgLy8gdXNlciBjcmVkZW50aWFsczogdXNlcm5hbWUsIGVtYWlsLCBwYXNzd29yZFxuICAgIGVycm9yOiB7fSxcbn07XG5jb25zdCByZWR1Y2VyID0gKHN0YXRlLCBhY3Rpb24pID0+IHtcbiAgICBzd2l0Y2ggKGFjdGlvbi50eXBlKSB7XG4gICAgICAgIGNhc2UgJ1JFU0VUJzpcbiAgICAgICAgICAgIHJldHVybiB7IC4uLmluaXRpYWxTdGF0ZSB9O1xuICAgICAgICBjYXNlICdUT0dHTEUnOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5pbml0aWFsU3RhdGUsXG4gICAgICAgICAgICAgICAgaXNPcGVuOiAhc3RhdGUuaXNPcGVuLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgLy8gRm9ybSBzdWJtaXRcbiAgICAgICAgY2FzZSAnU1VCTUlUJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiB0cnVlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgICAgIHVzZXI6IHtcbiAgICAgICAgICAgICAgICAgICAgLi4uc3RhdGUudXNlcixcbiAgICAgICAgICAgICAgICAgICAgW2FjdGlvbi5pbnB1dE5hbWVdOiBhY3Rpb24uaW5wdXRWYWx1ZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfTtcbiAgICAgICAgLy8gU3VibWl0IHJlc3VsdCBzdWNjZXNzXG4gICAgICAgIGNhc2UgJ05FWFQnOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGN1cnJlbnQ6IGFjdGlvbi5jdXJyZW50LFxuICAgICAgICAgICAgICAgIHVzZXI6IHtcbiAgICAgICAgICAgICAgICAgICAgLi4uc3RhdGUudXNlcixcbiAgICAgICAgICAgICAgICAgICAgW2FjdGlvbi5pbnB1dE5hbWVdOiBhY3Rpb24uaW5wdXRWYWx1ZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnUkVHSVNURVInOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgICAgIG1vZGU6ICdyZWdpc3RlcicsXG4gICAgICAgICAgICB9O1xuICAgICAgICBjYXNlICdMT0dJTl9TVUNDRVNTJzpcbiAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5yZWxvYWQoKTtcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ0VSUk9SJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNMb2FkaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiB0cnVlLFxuICAgICAgICAgICAgICAgIGVycm9yOiBhY3Rpb24uZXJyb3IgPyBhY3Rpb24uZXJyb3IgOiB7IG1lc3NhZ2U6ICdBbiBlcnJvciBvY2N1cnJlZC4nIH0sXG4gICAgICAgICAgICB9O1xuICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKCk7XG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gTG9naW4oeyBMb2dpbkJ1dHRvbiB9KSB7XG4gICAgY29uc3QgZm9ybSA9IFJlYWN0LnVzZVJlZigpO1xuXG4gICAgY29uc3QgW3N0YXRlLCBkaXNwYXRjaFN0YXRlXSA9IFJlYWN0LnVzZVJlZHVjZXIocmVkdWNlciwgaW5pdGlhbFN0YXRlKTtcblxuICAgIGNvbnN0IHRvZ2dsZU9wZW4gPSAoZXZlbnQpID0+IHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgZGlzcGF0Y2hTdGF0ZSh7IHR5cGU6ICdUT0dHTEUnIH0pO1xuICAgIH07XG5cbiAgICBjb25zdCByZXNldEZvcm0gPSAoKSA9PiB7XG4gICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnUkVTRVQnIH0pO1xuICAgIH07XG5cbiAgICBjb25zdCBoYW5kbGVTdWJtaXQgPSBhc3luYyAoZXZlbnQpID0+IHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICBjb25zdCBpbnB1dCA9IGZvcm1bJ2N1cnJlbnQnXVtzdGF0ZS5jdXJyZW50XVsndmFsdWUnXTtcblxuICAgICAgICBkaXNwYXRjaFN0YXRlKHtcbiAgICAgICAgICAgIHR5cGU6ICdTVUJNSVQnLFxuICAgICAgICAgICAgaW5wdXROYW1lOiBzdGF0ZS5jdXJyZW50ID09PSAndXNlcm5hbWUnICYmIGlucHV0LmluY2x1ZGVzKCdAJykgPyAnZW1haWwnIDogc3RhdGUuY3VycmVudCxcbiAgICAgICAgICAgIGlucHV0VmFsdWU6IGlucHV0LFxuICAgICAgICAgICAgY3VycmVudDogJ3Bhc3N3b3JkJyxcbiAgICAgICAgfSk7XG5cbiAgICAgICAgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICd1c2VybmFtZScpIHtcbiAgICAgICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goYCR7QVBJX0VORFBPSU5UfS8ke2lucHV0fWAsIHtcbiAgICAgICAgICAgICAgICBtZXRob2Q6ICdHRVQnLFxuICAgICAgICAgICAgICAgIG1vZGU6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIGlmIChyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgIGNvbnN0IHJlc3VsdCA9IGF3YWl0IHJlc3BvbnNlLmpzb24oKTtcbiAgICAgICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHtcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogJ05FWFQnLFxuICAgICAgICAgICAgICAgICAgICBpbnB1dE5hbWU6ICd1c2VybmFtZScsXG4gICAgICAgICAgICAgICAgICAgIGlucHV0VmFsdWU6IHJlc3VsdC5jb2xsZWN0aW9uLml0ZW1zWzBdLnVzZXJuYW1lLFxuICAgICAgICAgICAgICAgICAgICBjdXJyZW50OiAncGFzc3dvcmQnLFxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAvLyBVc2VybmFtZSBvciBlbWFpbCBub3QgZm91bmQ7IE9mZmVyIHJlZ2lzdHJhdGlvblxuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnUkVHSVNURVInIH0pO1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnRVJST1InLCBlcnJvcjogeyBtZXNzYWdlOiAnV2UgZG9uXFwndCBoYXZlIGFueW9uZSByZWdpc3RlcmVkIGJ5IHRoYXQgbmFtZS4gV291bGQgeW91IGxpa2UgdG8gcmVnaXN0ZXI/JyB9IH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICdwYXNzd29yZCcpIHtcbiAgICAgICAgICAgIGNvbnN0IHBheWxvYWQgPSB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUudXNlcixcbiAgICAgICAgICAgICAgICBwYXNzd29yZDogaW5wdXQsXG4gICAgICAgICAgICB9O1xuXG4gICAgICAgICAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGZldGNoKEFQSV9FTkRQT0lOVCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ1BPU1QnLFxuICAgICAgICAgICAgICAgIG1vZGU6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICAgICAgaGVhZGVyczoge1xuICAgICAgICAgICAgICAgICAgICAnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL2pzb24nLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgYm9keTogSlNPTi5zdHJpbmdpZnkocGF5bG9hZCksXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIGlmIChyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnTE9HSU5fU1VDQ0VTUycgfSk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGNvbnN0IHJlc3VsdCA9IGF3YWl0IHJlc3BvbnNlLmpzb24oKTtcbiAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhyZXNwb25zZSwgcmVzdWx0KTtcbiAgICAgICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ0VSUk9SJywgZXJyb3I6IHJlc3VsdC5jb2xsZWN0aW9uLmVycm9yc1swXSB9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnRVJST1InLCBlcnJvcjogJ0FuIHVua25vd24gZXJyb3Igb2NjdXJyZWQuJyB9KTtcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICBsZXQgbWVzc2FnZTtcbiAgICBpZiAoc3RhdGUuaXNFcnJvcikge1xuICAgICAgICBtZXNzYWdlID0gPGRpdiBjbGFzc05hbWU9XCJlcnJvclwiPntzdGF0ZS5lcnJvci5oYXNPd25Qcm9wZXJ0eSgnbWVzc2FnZScpID8gc3RhdGUuZXJyb3IubWVzc2FnZSA6ICdBbiBlcnJvciBvY2N1cnJlZCd9PC9kaXY+O1xuICAgIH0gZWxzZSBpZiAoc3RhdGUubW9kZSA9PT0gJ2xvZ2luJykge1xuICAgICAgICBpZiAoc3RhdGUuY3VycmVudCA9PT0gJ3VzZXJuYW1lJykgbWVzc2FnZSA9IFwiRXJtLi4uIFdoYXQncyB5b3VyIG5hbWUgYWdhaW4/XCI7XG4gICAgICAgIGVsc2UgaWYgKHN0YXRlLmN1cnJlbnQgPT09ICdwYXNzd29yZCcpIG1lc3NhZ2UgPSBgVGhhdCdzIHJpZ2h0ISBJIHJlbWVtYmVyIG5vdyEgWW91ciBuYW1lIGlzICR7c3RhdGUudXNlci51c2VybmFtZX0hYDtcbiAgICB9IGVsc2UgaWYgKHN0YXRlLm1vZGUgPT09ICdyZWdpc3RlcicpIHtcbiAgICAgICAgLy8gbWVzc2FnZSA9ICdSZWdpc3RlciBoZXJlJztcbiAgICB9XG5cbiAgICBjb25zdCBMb2dpbkZvcm0gPSAoKSA9PiAoXG4gICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tbG9naW5cIj5cbiAgICAgICAgICAgIDxVbmRlcmxpbmVkSW5wdXQgbmFtZT1cInVzZXJuYW1lXCIgcGxhY2Vob2xkZXI9XCJVc2VybmFtZSBvciBFbWFpbFwiIHBhZGRpbmc9ezE5fSBhdXRvZm9jdXM9e3N0YXRlLmN1cnJlbnQgPT09ICd1c2VybmFtZSd9IGhpZGRlbj17c3RhdGUuY3VycmVudCAhPT0gJ3VzZXJuYW1lJ30gLz5cbiAgICAgICAgICAgIDxVbmRlcmxpbmVkSW5wdXQgdHlwZT1cInBhc3N3b3JkXCIgbmFtZT1cInBhc3N3b3JkXCIgcGxhY2Vob2xkZXI9XCJQYXNzd29yZFwiIHBhZGRpbmc9ezE5fSBhdXRvZm9jdXM9e3N0YXRlLmN1cnJlbnQgPT09ICdwYXNzd29yZCd9IGhpZGRlbj17c3RhdGUuY3VycmVudCAhPT0gJ3Bhc3N3b3JkJ30gLz5cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcblxuICAgIGNvbnN0IFJlZ2lzdGVyRm9ybSA9ICgpID0+IChcbiAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1yZWdpc3RlclwiPlxuICAgICAgICAgICAgPFVuZGVybGluZWRJbnB1dCBuYW1lPVwidXNlcm5hbWVcIiB2YWx1ZT17c3RhdGUudXNlci51c2VybmFtZX0gcGxhY2Vob2xkZXI9XCJVc2VybmFtZVwiIHBhZGRpbmc9ezE5fSBhdXRvZm9jdXMgLz5cbiAgICAgICAgICAgIDxVbmRlcmxpbmVkSW5wdXQgbmFtZT1cImVtYWlsXCIgdmFsdWU9e3N0YXRlLnVzZXIuZW1haWx9IHBsYWNlaG9sZGVyPVwiRW1haWxcIiBwYWRkaW5nPXsxOX0gLz5cbiAgICAgICAgICAgIDxVbmRlcmxpbmVkSW5wdXQgdHlwZT1cInBhc3N3b3JkXCIgbmFtZT1cInBhc3N3b3JkXCIgcGxhY2Vob2xkZXI9XCJQYXNzd29yZFwiIHBhZGRpbmc9ezE5fSAvPlxuICAgICAgICA8L2Rpdj5cbiAgICApO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPD5cbiAgICAgICAgICAgIDxMb2dpbkJ1dHRvbiBoYW5kbGVDbGljaz17dG9nZ2xlT3Blbn0gLz5cbiAgICAgICAgICAgIDxNb2RhbCBvcGVuPXtzdGF0ZS5pc09wZW59IGNsb3NlPXt0b2dnbGVPcGVufT5cbiAgICAgICAgICAgICAgICA8Zm9ybSBpZD1cImxvZ2luZm9ybVwiIHJlZj17Zm9ybX0gb25TdWJtaXQ9e2hhbmRsZVN1Ym1pdH0gY2xhc3NOYW1lPXtzdGF0ZS5pc0xvYWRpbmcgPyAnbG9hZGluZycgOiB1bmRlZmluZWR9PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLW5hdlwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPE5hdk1lbnU+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPE5hdk1lbnUuSXRlbSBzZWxlY3RlZD48YSBocmVmPVwiI2xvZ2luXCIgb25DbGljaz17cmVzZXRGb3JtfT5OZXcgTmFtZTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjYmx1ZVwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+Qmx1ZTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjZ2FyeVwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+R2FyeTwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8TmF2TWVudS5JdGVtPjxhIGhyZWY9XCIjam9oblwiIG9uQ2xpY2s9e3Jlc2V0Rm9ybX0+Sm9objwvYT48L05hdk1lbnUuSXRlbT5cbiAgICAgICAgICAgICAgICAgICAgICAgIDwvTmF2TWVudT5cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tcml2YWxcIiAvPlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibG9naW5mb3JtLW1lc3NhZ2VcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHttZXNzYWdlfVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBpZD1cImxvZ2luZm9ybS1pbnB1dFwiPlxuICAgICAgICAgICAgICAgICAgICAgICAge3N0YXRlLm1vZGUgPT09ICdsb2dpbicgPyA8TG9naW5Gb3JtIC8+IDogPFJlZ2lzdGVyRm9ybSAvPn1cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgaWQ9XCJsb2dpbmZvcm0tc3VibWl0XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICB7c3RhdGUuaXNMb2FkaW5nID8gKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDw+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuPk9hayBpcyB0aGlua2luZzwvc3Bhbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICAgICAgICAgICkgOiAoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPD5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge3N0YXRlLm1vZGUgPT09ICdsb2dpbidcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgID8gPEJ1dHRvbiBvbkNsaWNrPXsoKSA9PiBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ1JFR0lTVEVSJyB9KX0+UmVnaXN0ZXI8L0J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogPEJ1dHRvbiBvbkNsaWNrPXtyZXNldEZvcm19PkxvZ2luPC9CdXR0b24+fVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8QnV0dG9uIHZhcmlhbnQ9XCJjb250YWluZWRcIiB0eXBlPVwic3VibWl0XCI+U3VibWl0PC9CdXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC8+XG4gICAgICAgICAgICAgICAgICAgICAgICApfVxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICA8L2Zvcm0+XG4gICAgICAgICAgICA8L01vZGFsPlxuICAgICAgICA8Lz5cbiAgICApO1xufVxuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307IiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCAnLi9VbmRlcmxpbmVkSW5wdXQuY3NzJztcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVW5kZXJsaW5lZElucHV0KHtcbiAgICBuYW1lLFxuICAgIHZhbHVlOiBpbml0aWFsVmFsdWUgPSAnJyxcbiAgICB0eXBlID0gJ3RleHQnLFxuICAgIHBhZGRpbmcgPSAxMCxcbiAgICBhdXRvZm9jdXMsXG4gICAgcGxhY2Vob2xkZXI6IHBsYWNlaG9sZGVyVmFsdWUsXG4gICAgLi4ucHJvcHNcbn0pIHtcbiAgICBjb25zdCBbdmFsdWUsIHNldFZhbHVlXSA9IFJlYWN0LnVzZVN0YXRlKGluaXRpYWxWYWx1ZSk7XG4gICAgY29uc3QgaGFuZGxlQ2hhbmdlID0gKGV2ZW50KSA9PiB7XG4gICAgICAgIHNldFZhbHVlKGV2ZW50LnRhcmdldC52YWx1ZSk7XG4gICAgfTtcbiAgICBjb25zdCBwbGFjZWhvbGRlclBhZCA9IHBhZGRpbmcgLSB2YWx1ZS5sZW5ndGg7XG4gICAgY29uc3QgcGxhY2Vob2xkZXIgPSAnXycucmVwZWF0KHBsYWNlaG9sZGVyUGFkID4gMCA/IHBsYWNlaG9sZGVyUGFkIDogMCk7XG4gICAgY29uc3Qgdmlld1ZhbHVlID0gdHlwZSA9PT0gJ3Bhc3N3b3JkJyA/ICcqJy5yZXBlYXQodmFsdWUubGVuZ3RoKSA6IHZhbHVlO1xuXG4gICAgaWYgKGF1dG9mb2N1cykge1xuICAgICAgICBwcm9wcy5yZWYgPSAoaW5wdXQpID0+IGlucHV0ICYmIGlucHV0LmZvY3VzKCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJ1bmRlcmxpbmVkaW5wdXRcIiB7Li4ucHJvcHN9PlxuICAgICAgICAgICAgPGlucHV0IHR5cGU9e3R5cGV9IG5hbWU9e25hbWV9IHZhbHVlPXt2YWx1ZX0gb25DaGFuZ2U9e2hhbmRsZUNoYW5nZX0gY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0LXR5cG9ncmFwaHlcIiAvPlxuICAgICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJ1bmRlcmxpbmVkaW5wdXQtdmlld1wiPlxuICAgICAgICAgICAgICAgIHt2YWx1ZSA/IChcbiAgICAgICAgICAgICAgICAgICAgPD5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzTmFtZT1cInVuZGVybGluZWRpbnB1dC12YWx1ZVwiPnt2aWV3VmFsdWV9PC9zcGFuPlxuICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0LWNhcmF0XCI+Jm5ic3A7PC9zcGFuPlxuICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICApIDogKFxuICAgICAgICAgICAgICAgICAgICA8PlxuICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0LWNhcmF0XCI+Jm5ic3A7PC9zcGFuPlxuICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidW5kZXJsaW5lZGlucHV0LXBsYWNlaG9sZGVyXCI+e3BsYWNlaG9sZGVyVmFsdWV9PC9zcGFuPlxuICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICApfVxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cInVuZGVybGluZWRpbnB1dC11bmRlcmxpbmVcIj5cbiAgICAgICAgICAgICAgICA8c3BhbiBzdHlsZT17eyBvcGFjaXR5OiAwIH19Pnt2aWV3VmFsdWV9PC9zcGFuPlxuICAgICAgICAgICAgICAgIDxzcGFuPntwbGFjZWhvbGRlcn08L3NwYW4+XG4gICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=