(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

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
/* harmony import */ var _lib_icons_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../lib/icons.js */ "./browser/src/lib/icons.js");
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
  error: {}
};

const reducer = (state, action) => {
  switch (action.type) {
    case 'TOGGLE':
      return { ...state,
        isOpen: !state.isOpen
      };

    case 'INIT':
      return { ...state,
        isLoading: true,
        isError: false
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
    const payload = {
      password: form.current.password.value
    };
    const userInput = form.current.username.value;

    if (userInput.includes('@')) {
      payload.email = userInput;
    } else {
      payload.username = userInput;
    }

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
  };

  const LoginButton = () => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php",
    title: "Login",
    onClick: toggleOpen,
    className: "user user-unknown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_2__["QuestionBlock"], {
    className: "user-avatar thumbnail"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, "Login"));

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(LoginButton, null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: state.isOpen,
    close: toggleOpen,
    closeButton: false
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("form", {
    ref: form,
    onSubmit: handleSubmit,
    className: state.isLoading ? 'loading' : undefined
  }, state.isError && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "error"
  }, state.error.hasOwnProperty('message') ? state.error.message : 'An error occurred'), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "text",
    name: "username",
    placeholder: "Username or Email",
    ref: input => input && input.focus()
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "password",
    name: "password",
    placeholder: "Password"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, state.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_lib_icons_js__WEBPACK_IMPORTED_MODULE_2__["LoadingMascot"], {
    className: "loading"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, "Processing login")) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "submit",
    disabled: state.isLoading
  }, "Login"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: toggleOpen
  }, "Cancel"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/retrieve-pass.php"
  }, "Reset password")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/register.php"
  }, "Register a new account"))))));
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0xvZ2luLmpzeCJdLCJuYW1lcyI6WyJjb25zb2xlIiwibG9nIiwiQVBJX0VORFBPSU5UIiwicHJvY2VzcyIsImluaXRpYWxTdGF0ZSIsImlzT3BlbiIsImlzTG9hZGluZyIsImlzRXJyb3IiLCJlcnJvciIsInJlZHVjZXIiLCJzdGF0ZSIsImFjdGlvbiIsInR5cGUiLCJ3aW5kb3ciLCJsb2NhdGlvbiIsInJlbG9hZCIsIm1lc3NhZ2UiLCJFcnJvciIsIkxvZ2luIiwiZm9ybSIsIlJlYWN0IiwidXNlUmVmIiwiZGlzcGF0Y2hTdGF0ZSIsInVzZVJlZHVjZXIiLCJ0b2dnbGVPcGVuIiwiZXZlbnQiLCJwcmV2ZW50RGVmYXVsdCIsImhhbmRsZVN1Ym1pdCIsInBheWxvYWQiLCJwYXNzd29yZCIsImN1cnJlbnQiLCJ2YWx1ZSIsInVzZXJJbnB1dCIsInVzZXJuYW1lIiwiaW5jbHVkZXMiLCJlbWFpbCIsInJlc3BvbnNlIiwiZmV0Y2giLCJtZXRob2QiLCJtb2RlIiwiY3JlZGVudGlhbHMiLCJoZWFkZXJzIiwiYm9keSIsIkpTT04iLCJzdHJpbmdpZnkiLCJvayIsInJlc3VsdCIsImpzb24iLCJjb2xsZWN0aW9uIiwiZXJyb3JzIiwiTG9naW5CdXR0b24iLCJ1bmRlZmluZWQiLCJoYXNPd25Qcm9wZXJ0eSIsImlucHV0IiwiZm9jdXMiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTs7QUFFQTs7OztBQUtBO0FBQ0E7QUFDQTtBQUVBQSxPQUFPLENBQUNDLEdBQVIsQ0FBWSwrQkFBWjtBQUVBLE1BQU1DLFlBQVksR0FBSSxHQUFFQyxNQUF5QixRQUFqRDtBQUVBLE1BQU1DLFlBQVksR0FBRztBQUNqQkMsUUFBTSxFQUFFLElBRFM7QUFFakJDLFdBQVMsRUFBRSxLQUZNO0FBR2pCQyxTQUFPLEVBQUUsS0FIUTtBQUlqQkMsT0FBSyxFQUFFO0FB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UyxFQUFDO0FBQXpCLElBREosZUFFSSw0RkFGSixDQURILGdCQU1HLHFJQUNJO0FBQVEsUUFBSSxFQUFDLFFBQWI7QUFBc0IsWUFBUSxFQUFFSSxLQUFLLENBQUNKO0FBQXRDLGFBREosZUFFSTtBQUFRLFFBQUksRUFBQyxRQUFiO0FBQXNCLFdBQU8sRUFBRWtCO0FBQS9CLGNBRkosQ0FQUixDQUpKLGVBaUJJLG9GQUNJLG9GQUFJO0FBQUcsUUFBSSxFQUFDO0FBQVIsc0JBQUosQ0FESixlQUVJLG9GQUFJO0FBQUcsUUFBSSxFQUFDO0FBQVIsOEJBQUosQ0FGSixDQWpCSixDQURKLENBRkosQ0FESjtBQTZCSCxDIiwiZmlsZSI6IjBfYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyogZXNsaW50LWRpc2FibGUgbm8tcHJvdG90eXBlLWJ1aWx0aW5zICovXG5cbi8qKlxuICogTG9naW4gY29tcG9uZW50IHdpdGggbG9naW4gZm9ybVxuICogTm90ZTogQ29tcG9uZW50IGlzIGxhenkgbG9hZGVkIHVwb24gY2xpY2tpbmcgbG9naW4gYnV0dG9uXG4gKi9cblxuaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBNb2RhbCBmcm9tICcuL01vZGFsLmpzeCc7XG5pbXBvcnQgeyBRdWVzdGlvbkJsb2NrLCBMb2FkaW5nTWFzY290IH0gZnJvbSAnLi4vbGliL2ljb25zLmpzJztcblxuY29uc29sZS5sb2coJzxMb2dpbj4gaGFzIGJlZW4gbGF6eSBsb2FkZWQhJyk7XG5cbmNvbnN0IEFQSV9FTkRQT0lOVCA9IGAke3Byb2Nlc3MuZW52LkFQSV9FTkRQT0lOVH0vbG9naW5gO1xuXG5jb25zdCBpbml0aWFsU3RhdGUgPSB7XG4gICAgaXNPcGVuOiB0cnVlLFxuICAgIGlzTG9hZGluZzogZmFsc2UsXG4gICAgaXNFcnJvcjogZmFsc2UsXG4gICAgZXJyb3I6IHt9LFxufTtcbmNvbnN0IHJlZHVjZXIgPSAoc3RhdGUsIGFjdGlvbikgPT4ge1xuICAgIHN3aXRjaCAoYWN0aW9uLnR5cGUpIHtcbiAgICAgICAgY2FzZSAnVE9HR0xFJzpcbiAgICAgICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAgICAgLi4uc3RhdGUsXG4gICAgICAgICAgICAgICAgaXNPcGVuOiAhc3RhdGUuaXNPcGVuLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnSU5JVCc6XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIC4uLnN0YXRlLFxuICAgICAgICAgICAgICAgIGlzTG9hZGluZzogdHJ1ZSxcbiAgICAgICAgICAgICAgICBpc0Vycm9yOiBmYWxzZSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGNhc2UgJ0xPR0lOX1NVQ0NFU1MnOlxuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpO1xuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IGZhbHNlLFxuICAgICAgICAgICAgfTtcbiAgICAgICAgY2FzZSAnTE9HSU5fRVJST1InOlxuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICAuLi5zdGF0ZSxcbiAgICAgICAgICAgICAgICBpc0xvYWRpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGlzRXJyb3I6IHRydWUsXG4gICAgICAgICAgICAgICAgZXJyb3I6IGFjdGlvbi5lcnJvciA/IGFjdGlvbi5lcnJvciA6IHsgbWVzc2FnZTogJ0FuIGVycm9yIG9jY3VycmVkLicgfSxcbiAgICAgICAgICAgIH07XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoKTtcbiAgICB9XG59O1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBMb2dpbigpIHtcbiAgICBjb25zdCBmb3JtID0gUmVhY3QudXNlUmVmKCk7XG5cbiAgICBjb25zdCBbc3RhdGUsIGRpc3BhdGNoU3RhdGVdID0gUmVhY3QudXNlUmVkdWNlcihyZWR1Y2VyLCBpbml0aWFsU3RhdGUpO1xuXG4gICAgY29uc3QgdG9nZ2xlT3BlbiA9IChldmVudCkgPT4ge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ1RPR0dMRScgfSk7XG4gICAgfTtcblxuICAgIGNvbnN0IGhhbmRsZVN1Ym1pdCA9IGFzeW5jIChldmVudCkgPT4ge1xuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgIGRpc3BhdGNoU3RhdGUoeyB0eXBlOiAnSU5JVCcgfSk7XG5cbiAgICAgICAgY29uc3QgcGF5bG9hZCA9IHtcbiAgICAgICAgICAgIHBhc3N3b3JkOiBmb3JtLmN1cnJlbnQucGFzc3dvcmQudmFsdWUsXG4gICAgICAgIH07XG4gICAgICAgIGNvbnN0IHVzZXJJbnB1dCA9IGZvcm0uY3VycmVudC51c2VybmFtZS52YWx1ZTtcbiAgICAgICAgaWYgKHVzZXJJbnB1dC5pbmNsdWRlcygnQCcpKSB7XG4gICAgICAgICAgICBwYXlsb2FkLmVtYWlsID0gdXNlcklucHV0O1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgcGF5bG9hZC51c2VybmFtZSA9IHVzZXJJbnB1dDtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goQVBJX0VORFBPSU5ULCB7XG4gICAgICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgICAgIG1vZGU6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgICAgICAgICAnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL2pzb24nLFxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGJvZHk6IEpTT04uc3RyaW5naWZ5KHBheWxvYWQpLFxuICAgICAgICB9KTtcbiAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ0xPR0lOX1NVQ0NFU1MnIH0pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgY29uc29sZS5sb2cocmVzcG9uc2UsIHJlc3VsdCk7XG4gICAgICAgICAgICBkaXNwYXRjaFN0YXRlKHsgdHlwZTogJ0xPR0lOX0VSUk9SJywgZXJyb3I6IHJlc3VsdC5jb2xsZWN0aW9uLmVycm9yc1swXSB9KTtcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICBjb25zdCBMb2dpbkJ1dHRvbiA9ICgpID0+IChcbiAgICAgICAgPGEgaHJlZj1cIi9sb2dpbi5waHBcIiB0aXRsZT1cIkxvZ2luXCIgb25DbGljaz17dG9nZ2xlT3Blbn0gY2xhc3NOYW1lPVwidXNlciB1c2VyLXVua25vd25cIj5cbiAgICAgICAgICAgIDxRdWVzdGlvbkJsb2NrIGNsYXNzTmFtZT1cInVzZXItYXZhdGFyIHRodW1ibmFpbFwiIC8+XG4gICAgICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyLXVzZXJuYW1lXCI+TG9naW48L3NwYW4+XG4gICAgICAgIDwvYT5cbiAgICApO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPD5cbiAgICAgICAgICAgIDxMb2dpbkJ1dHRvbiAvPlxuICAgICAgICAgICAgPE1vZGFsIG9wZW49e3N0YXRlLmlzT3Blbn0gY2xvc2U9e3RvZ2dsZU9wZW59IGNsb3NlQnV0dG9uPXtmYWxzZX0+XG4gICAgICAgICAgICAgICAgPGZvcm0gcmVmPXtmb3JtfSBvblN1Ym1pdD17aGFuZGxlU3VibWl0fSBjbGFzc05hbWU9e3N0YXRlLmlzTG9hZGluZyA/ICdsb2FkaW5nJyA6IHVuZGVmaW5lZH0+XG4gICAgICAgICAgICAgICAgICAgIHtzdGF0ZS5pc0Vycm9yICYmIDxkaXYgY2xhc3NOYW1lPVwiZXJyb3JcIj57c3RhdGUuZXJyb3IuaGFzT3duUHJvcGVydHkoJ21lc3NhZ2UnKSA/IHN0YXRlLmVycm9yLm1lc3NhZ2UgOiAnQW4gZXJyb3Igb2NjdXJyZWQnfTwvZGl2Pn1cbiAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJ0ZXh0XCIgbmFtZT1cInVzZXJuYW1lXCIgcGxhY2Vob2xkZXI9XCJVc2VybmFtZSBvciBFbWFpbFwiIHJlZj17KGlucHV0KSA9PiBpbnB1dCAmJiBpbnB1dC5mb2N1cygpfSAvPlxuICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT1cInBhc3N3b3JkXCIgbmFtZT1cInBhc3N3b3JkXCIgcGxhY2Vob2xkZXI9XCJQYXNzd29yZFwiIC8+XG4gICAgICAgICAgICAgICAgICAgIDxkaXY+XG4gICAgICAgICAgICAgICAgICAgICAgICB7c3RhdGUuaXNMb2FkaW5nID8gKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDw+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxMb2FkaW5nTWFzY290IGNsYXNzTmFtZT1cImxvYWRpbmdcIiAvPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3Bhbj5Qcm9jZXNzaW5nIGxvZ2luPC9zcGFuPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvPlxuICAgICAgICAgICAgICAgICAgICAgICAgKSA6IChcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8PlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJzdWJtaXRcIiBkaXNhYmxlZD17c3RhdGUuaXNMb2FkaW5nfT5Mb2dpbjwvYnV0dG9uPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBvbkNsaWNrPXt0b2dnbGVPcGVufT5DYW5jZWw8L2J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Lz5cbiAgICAgICAgICAgICAgICAgICAgICAgICl9XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8dWw+XG4gICAgICAgICAgICAgICAgICAgICAgICA8bGk+PGEgaHJlZj1cIi9yZXRyaWV2ZS1wYXNzLnBocFwiPlJlc2V0IHBhc3N3b3JkPC9hPjwvbGk+XG4gICAgICAgICAgICAgICAgICAgICAgICA8bGk+PGEgaHJlZj1cIi9yZWdpc3Rlci5waHBcIj5SZWdpc3RlciBhIG5ldyBhY2NvdW50PC9hPjwvbGk+XG4gICAgICAgICAgICAgICAgICAgIDwvdWw+XG4gICAgICAgICAgICAgICAgPC9mb3JtPlxuICAgICAgICAgICAgPC9Nb2RhbD5cbiAgICAgICAgPC8+XG4gICAgKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=