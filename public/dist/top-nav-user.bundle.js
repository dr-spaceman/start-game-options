(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["top-nav-user"],{

/***/ "./browser/src/components/User.jsx":
/*!*****************************************!*\
  !*** ./browser/src/components/User.jsx ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return User; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

function User(props) {
  let {
    username,
    avatar,
    href
  } = props;

  if (href === undefined) {
    href = `~${username}`;
  }

  let tag;
  const tagChild = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, username);

  if (href) {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('a', {
      href,
      className: 'user-link'
    }, tagChild);
  } else {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('span', {
      className: 'user-link'
    }, tagChild);
  }

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user"
  }, tag);
}

/***/ }),

/***/ "./browser/src/components/layout/TopNavUser.jsx":
/*!******************************************************!*\
  !*** ./browser/src/components/layout/TopNavUser.jsx ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return TopNavUser; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ui/Dropdown.jsx */ "./browser/src/components/ui/Dropdown.jsx");
/* harmony import */ var _User_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../User.jsx */ "./browser/src/components/User.jsx");
/* harmony import */ var _ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui/Button.jsx */ "./browser/src/components/ui/Button.jsx");




console.log('<TopNavUser> has been lazy loaded!');

function logout() {
  fetch('/logout.php').then(response => {
    if (response.ok) {
      window.location.reload();
    }
  });
}

function TopNavUser({
  username
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: "login-user-dropdown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Toggle, {
    classes: {
      'button-header': true
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_User_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    username: username,
    href: "",
    avatar: ""
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Menu, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}`
  }, "Profile")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}/games`
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Button_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    variant: "link",
    onClick: logout
  }, "Log out"))));
}

/***/ }),

/***/ "./browser/src/components/ui/Dropdown.jsx":
/*!************************************************!*\
  !*** ./browser/src/components/ui/Dropdown.jsx ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../lib/match-component.js */ "./browser/src/lib/match-component.js");
/* harmony import */ var _hooks_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../hooks/use-outside-click.js */ "./browser/src/hooks/use-outside-click.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isDropdownToggle = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownToggle);
const isDropdownMenu = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownMenu);

function Dropdown({
  classes,
  children,
  ...props
}) {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleToggle = () => setOpen(!open);

  const handleClose = () => setOpen(false);

  const ref = Object(_hooks_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__["default"])(handleClose);
  const className = classnames__WEBPACK_IMPORTED_MODULE_1___default()({ ...classes,
    dropdown: true,
    open
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    ref: ref,
    className: className
  }, props), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, child => {
    if (! /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.isValidElement(child)) {
      return child;
    } // Button toggle dropdown menu


    if (isDropdownToggle(child)) {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.cloneElement(child, {
        handleClick: handleToggle
      });
    }

    return child;
  }));
}

function DropdownToggle({
  classes,
  children,
  handleClick
}) {
  const className = classnames__WEBPACK_IMPORTED_MODULE_1___default()({ ...classes,
    'dropdown-toggle': true
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    className: className,
    onClick: handleClick,
    "aria-haspopup": "true",
    "aria-expanded": "true"
  }, children);
}

function DropdownMenu({
  classes,
  children
}) {
  const className = classnames__WEBPACK_IMPORTED_MODULE_1___default()({ ...classes,
    'dropdown-menu': true,
    light: true
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: className,
    role: "menu"
  }, children);
}

function DropdownItem({
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "dropdown-item",
    role: "menuitem"
  }, children);
}

Dropdown.Toggle = DropdownToggle;
Dropdown.Menu = DropdownMenu;
Dropdown.Item = DropdownItem;
/* harmony default export */ __webpack_exports__["default"] = (Dropdown);

/***/ }),

/***/ "./browser/src/hooks/use-outside-click.js":
/*!************************************************!*\
  !*** ./browser/src/hooks/use-outside-click.js ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useOutsideClick; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Hook that listens for clicks outside of a reference.
 *
 * @param {Function} callback Function to execute on successful outside click
 *
 * @returns {Reference} Reference to "inside" element
 */

function useOutsideClick(callback) {
  const ref = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();

  function handleClickOutside(event) {
    var _ref$current, _ref$current$contains;

    // If the click is registered outside the given ref, trigger cb
    if (!((_ref$current = ref.current) === null || _ref$current === void 0 ? void 0 : (_ref$current$contains = _ref$current.contains) === null || _ref$current$contains === void 0 ? void 0 : _ref$current$contains.call(_ref$current, event.target))) {
      console.log('Outside click detected', callback);
      callback(event);
    }
  }

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    // Bind the event listener
    document.addEventListener('click', handleClickOutside);
    console.log('Listening for outside click...');
    return function cleanup() {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('click', handleClickOutside);
      console.log('Remove outside click handler');
    };
  }, [ref]);
  return ref;
}

function useOutsideClick_OLD(ref, callback = () => {}) {
  function handleClickOutside(event) {
    var _ref$current2, _ref$current2$contain;

    // If the click is registered outside the given ref, trigger cb
    if (!((_ref$current2 = ref.current) === null || _ref$current2 === void 0 ? void 0 : (_ref$current2$contain = _ref$current2.contains) === null || _ref$current2$contain === void 0 ? void 0 : _ref$current2$contain.call(_ref$current2, event.target))) {
      console.log('Outside click detected', callback);
      callback(event);
    }
  }

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    // Bind the event listener
    document.addEventListener('click', handleClickOutside);
    console.log('Listening for outside click...');
    return () => {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('click', handleClickOutside);
      console.log('Remove outside click handler');
    };
  }, [callback]);
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1VzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvbGF5b3V0L1RvcE5hdlVzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvdWkvRHJvcGRvd24uanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2hvb2tzL3VzZS1vdXRzaWRlLWNsaWNrLmpzIl0sIm5hbWVzIjpbIlVzZXIiLCJwcm9wcyIsInVzZXJuYW1lIiwiYXZhdGFyIiwiaHJlZiIsInVuZGVmaW5lZCIsInRhZyIsInRhZ0NoaWxkIiwiUmVhY3QiLCJjcmVhdGVFbGVtZW50IiwiY2xhc3NOYW1lIiwiY29uc29sZSIsImxvZyIsImxvZ291dCIsImZldGNoIiwidGhlbiIsInJlc3BvbnNlIiwib2siLCJ3aW5kb3ciLCJsb2NhdGlvbiIsInJlbG9hZCIsIlRvcE5hdlVzZXIiLCJpc0Ryb3Bkb3duVG9nZ2xlIiwibWF0Y2hDb21wb25lbnQiLCJEcm9wZG93blRvZ2dsZSIsImlzRHJvcGRvd25NZW51IiwiRHJvcGRvd25NZW51IiwiRHJvcGRvd24iLCJjbGFzc2VzIiwiY2hpbGRyZW4iLCJvcGVuIiwic2V0T3BlbiIsInVzZVN0YXRlIiwiaGFuZGxlVG9nZ2xlIiwiaGFuZGxlQ2xvc2UiLCJyZWYiLCJ1c2VPdXRzaWRlQ2xpY2siLCJjbiIsImRyb3Bkb3duIiwiQ2hpbGRyZW4iLCJtYXAiLCJjaGlsZCIsImlzVmFsaWRFbGVtZW50IiwiY2xvbmVFbGVtZW50IiwiaGFuZGxlQ2xpY2siLCJsaWdodCIsIkRyb3Bkb3duSXRlbSIsIlRvZ2dsZSIsIk1lbnUiLCJJdGVtIiwiY2FsbGJhY2siLCJ1c2VSZWYiLCJoYW5kbGVDbGlja091dHNpZGUiLCJldmVudCIsImN1cnJlbnQiLCJjb250YWlucyIsInRhcmdldCIsInVzZUVmZmVjdCIsImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0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ImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIFVzZXIocHJvcHMpIHtcbiAgICBsZXQgeyB1c2VybmFtZSwgYXZhdGFyLCBocmVmIH0gPSBwcm9wcztcblxuICAgIGlmIChocmVmID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgaHJlZiA9IGB+JHt1c2VybmFtZX1gO1xuICAgIH1cblxuICAgIGxldCB0YWc7XG4gICAgY29uc3QgdGFnQ2hpbGQgPSA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyLXVzZXJuYW1lXCI+e3VzZXJuYW1lfTwvc3Bhbj47XG4gICAgaWYgKGhyZWYpIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnYScsIHsgaHJlZiwgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnc3BhbicsIHsgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidXNlclwiPlxuICAgICAgICAgICAge3RhZ31cbiAgICAgICAgPC9zcGFuPlxuICAgICk7XG59XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IERyb3Bkb3duIGZyb20gJy4uL3VpL0Ryb3Bkb3duLmpzeCc7XG5pbXBvcnQgVXNlciBmcm9tICcuLi9Vc2VyLmpzeCc7XG5pbXBvcnQgQnV0dG9uIGZyb20gJy4uL3VpL0J1dHRvbi5qc3gnO1xuXG5jb25zb2xlLmxvZygnPFRvcE5hdlVzZXI+IGhhcyBiZWVuIGxhenkgbG9hZGVkIScpO1xuXG5mdW5jdGlvbiBsb2dvdXQoKSB7XG4gICAgZmV0Y2goJy9sb2dvdXQucGhwJykudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24ucmVsb2FkKCk7XG4gICAgICAgIH1cbiAgICB9KTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVG9wTmF2VXNlcih7IHVzZXJuYW1lIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8RHJvcGRvd24gaWQ9XCJsb2dpbi11c2VyLWRyb3Bkb3duXCI+XG4gICAgICAgICAgICA8RHJvcGRvd24uVG9nZ2xlIGNsYXNzZXM9e3sgJ2J1dHRvbi1oZWFkZXInOiB0cnVlIH19PlxuICAgICAgICAgICAgICAgIDxVc2VyIHVzZXJuYW1lPXt1c2VybmFtZX0gaHJlZj1cIlwiIGF2YXRhcj1cIlwiIC8+XG4gICAgICAgICAgICA8L0Ryb3Bkb3duLlRvZ2dsZT5cbiAgICAgICAgICAgIDxEcm9wZG93bi5NZW51PlxuICAgICAgICAgICAgICAgIDxEcm9wZG93bi5JdGVtPjxhIGhyZWY9e2AvfiR7dXNlcm5hbWV9YH0+UHJvZmlsZTwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX0vZ2FtZXNgfT5HYW1lczwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgICAgIDxCdXR0b24gdmFyaWFudD1cImxpbmtcIiBvbkNsaWNrPXtsb2dvdXR9PkxvZyBvdXQ8L0J1dHRvbj5cbiAgICAgICAgICAgICAgICA8L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICA8L0Ryb3Bkb3duLk1lbnU+XG4gICAgICAgIDwvRHJvcGRvd24+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgY24gZnJvbSAnY2xhc3NuYW1lcyc7XG5pbXBvcnQgbWF0Y2hDb21wb25lbnQgZnJvbSAnLi4vLi4vbGliL21hdGNoLWNvbXBvbmVudC5qcyc7XG5pbXBvcnQgdXNlT3V0c2lkZUNsaWNrIGZyb20gJy4uLy4uL2hvb2tzL3VzZS1vdXRzaWRlLWNsaWNrLmpzJztcblxuY29uc3QgaXNEcm9wZG93blRvZ2dsZSA9IG1hdGNoQ29tcG9uZW50KERyb3Bkb3duVG9nZ2xlKTtcbmNvbnN0IGlzRHJvcGRvd25NZW51ID0gbWF0Y2hDb21wb25lbnQoRHJvcGRvd25NZW51KTtcblxuZnVuY3Rpb24gRHJvcGRvd24oeyBjbGFzc2VzLCBjaGlsZHJlbiwgLi4ucHJvcHMgfSkge1xuICAgIGNvbnN0IFtvcGVuLCBzZXRPcGVuXSA9IFJlYWN0LnVzZVN0YXRlKGZhbHNlKTtcbiAgICBjb25zdCBoYW5kbGVUb2dnbGUgPSAoKSA9PiBzZXRPcGVuKCFvcGVuKTtcbiAgICBjb25zdCBoYW5kbGVDbG9zZSA9ICgpID0+IHNldE9wZW4oZmFsc2UpO1xuXG4gICAgY29uc3QgcmVmID0gdXNlT3V0c2lkZUNsaWNrKGhhbmRsZUNsb3NlKTtcblxuICAgIGNvbnN0IGNsYXNzTmFtZSA9IGNuKHtcbiAgICAgICAgLi4uY2xhc3NlcyxcbiAgICAgICAgZHJvcGRvd246IHRydWUsXG4gICAgICAgIG9wZW4sXG4gICAgfSk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IHJlZj17cmVmfSBjbGFzc05hbWU9e2NsYXNzTmFtZX0gey4uLnByb3BzfT5cbiAgICAgICAgICAgIHsvKiBNYXAgY2hpbGRyZW4gJiBpbmplY3QgbGlzdGVuZXJzICovfVxuICAgICAgICAgICAge1JlYWN0LkNoaWxkcmVuLm1hcChjaGlsZHJlbiwgKGNoaWxkKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFSZWFjdC5pc1ZhbGlkRWxlbWVudChjaGlsZCkpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGNoaWxkO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIC8vIEJ1dHRvbiB0b2dnbGUgZHJvcGRvd24gbWVudVxuICAgICAgICAgICAgICAgIGlmIChpc0Ryb3Bkb3duVG9nZ2xlKGNoaWxkKSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gUmVhY3QuY2xvbmVFbGVtZW50KGNoaWxkLCB7XG4gICAgICAgICAgICAgICAgICAgICAgICBoYW5kbGVDbGljazogaGFuZGxlVG9nZ2xlLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICB9KX1cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25Ub2dnbGUoeyBjbGFzc2VzLCBjaGlsZHJlbiwgaGFuZGxlQ2xpY2sgfSkge1xuICAgIGNvbnN0IGNsYXNzTmFtZSA9IGNuKHtcbiAgICAgICAgLi4uY2xhc3NlcyxcbiAgICAgICAgJ2Ryb3Bkb3duLXRvZ2dsZSc6IHRydWUsXG4gICAgfSk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzc05hbWU9e2NsYXNzTmFtZX0gb25DbGljaz17aGFuZGxlQ2xpY2t9IGFyaWEtaGFzcG9wdXA9XCJ0cnVlXCIgYXJpYS1leHBhbmRlZD1cInRydWVcIj5cbiAgICAgICAgICAgIHtjaGlsZHJlbn1cbiAgICAgICAgPC9idXR0b24+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25NZW51KHsgY2xhc3NlcywgY2hpbGRyZW4gfSkge1xuICAgIGNvbnN0IGNsYXNzTmFtZSA9IGNuKHtcbiAgICAgICAgLi4uY2xhc3NlcyxcbiAgICAgICAgJ2Ryb3Bkb3duLW1lbnUnOiB0cnVlLFxuICAgICAgICBsaWdodDogdHJ1ZSxcbiAgICB9KTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPXtjbGFzc05hbWV9IHJvbGU9XCJtZW51XCI+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duSXRlbSh7IGNoaWxkcmVuIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImRyb3Bkb3duLWl0ZW1cIiByb2xlPVwibWVudWl0ZW1cIj57Y2hpbGRyZW59PC9kaXY+XG4gICAgKTtcbn1cblxuRHJvcGRvd24uVG9nZ2xlID0gRHJvcGRvd25Ub2dnbGU7XG5Ecm9wZG93bi5NZW51ID0gRHJvcGRvd25NZW51O1xuRHJvcGRvd24uSXRlbSA9IERyb3Bkb3duSXRlbTtcblxuZXhwb3J0IGRlZmF1bHQgRHJvcGRvd247XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuXG4vKipcbiAqIEhvb2sgdGhhdCBsaXN0ZW5zIGZvciBjbGlja3Mgb3V0c2lkZSBvZiBhIHJlZmVyZW5jZS5cbiAqXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBjYWxsYmFjayBGdW5jdGlvbiB0byBleGVjdXRlIG9uIHN1Y2Nlc3NmdWwgb3V0c2lkZSBjbGlja1xuICpcbiAqIEByZXR1cm5zIHtSZWZlcmVuY2V9IFJlZmVyZW5jZSB0byBcImluc2lkZVwiIGVsZW1lbnRcbiAqL1xuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gdXNlT3V0c2lkZUNsaWNrKGNhbGxiYWNrKSB7XG4gICAgY29uc3QgcmVmID0gUmVhY3QudXNlUmVmKCk7XG5cbiAgICBmdW5jdGlvbiBoYW5kbGVDbGlja091dHNpZGUoZXZlbnQpIHtcbiAgICAgICAgLy8gSWYgdGhlIGNsaWNrIGlzIHJlZ2lzdGVyZWQgb3V0c2lkZSB0aGUgZ2l2ZW4gcmVmLCB0cmlnZ2VyIGNiXG4gICAgICAgIGlmICghcmVmLmN1cnJlbnQ/LmNvbnRhaW5zPy4oZXZlbnQudGFyZ2V0KSkge1xuICAgICAgICAgICAgY29uc29sZS5sb2coJ091dHNpZGUgY2xpY2sgZGV0ZWN0ZWQnLCBjYWxsYmFjayk7XG4gICAgICAgICAgICBjYWxsYmFjayhldmVudCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBSZWFjdC51c2VFZmZlY3QoKCkgPT4ge1xuICAgICAgICAvLyBCaW5kIHRoZSBldmVudCBsaXN0ZW5lclxuICAgICAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgIGNvbnNvbGUubG9nKCdMaXN0ZW5pbmcgZm9yIG91dHNpZGUgY2xpY2suLi4nKTtcbiAgICAgICAgcmV0dXJuIGZ1bmN0aW9uIGNsZWFudXAoKSB7XG4gICAgICAgICAgICAvLyBDbGVhbiB1cCBiZWZvcmUgY2FsbGluZyB0aGUgZWZmZWN0IGFnYWluIG9uIHRoZSBuZXh0IHJlbmRlclxuICAgICAgICAgICAgZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2xpY2snLCBoYW5kbGVDbGlja091dHNpZGUpO1xuICAgICAgICAgICAgY29uc29sZS5sb2coJ1JlbW92ZSBvdXRzaWRlIGNsaWNrIGhhbmRsZXInKTtcbiAgICAgICAgfTtcbiAgICB9LCBbcmVmXSk7XG5cbiAgICByZXR1cm4gcmVmO1xufVxuXG5mdW5jdGlvbiB1c2VPdXRzaWRlQ2xpY2tfT0xEKHJlZiwgY2FsbGJhY2sgPSAoKSA9PiB7fSkge1xuICAgIGZ1bmN0aW9uIGhhbmRsZUNsaWNrT3V0c2lkZShldmVudCkge1xuICAgICAgICAvLyBJZiB0aGUgY2xpY2sgaXMgcmVnaXN0ZXJlZCBvdXRzaWRlIHRoZSBnaXZlbiByZWYsIHRyaWdnZXIgY2JcbiAgICAgICAgaWYgKCFyZWYuY3VycmVudD8uY29udGFpbnM/LihldmVudC50YXJnZXQpKSB7XG4gICAgICAgICAgICBjb25zb2xlLmxvZygnT3V0c2lkZSBjbGljayBkZXRlY3RlZCcsIGNhbGxiYWNrKTtcbiAgICAgICAgICAgIGNhbGxiYWNrKGV2ZW50KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIFJlYWN0LnVzZUVmZmVjdCgoKSA9PiB7XG4gICAgICAgIC8vIEJpbmQgdGhlIGV2ZW50IGxpc3RlbmVyXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgY29uc29sZS5sb2coJ0xpc3RlbmluZyBmb3Igb3V0c2lkZSBjbGljay4uLicpO1xuICAgICAgICByZXR1cm4gKCkgPT4ge1xuICAgICAgICAgICAgLy8gQ2xlYW4gdXAgYmVmb3JlIGNhbGxpbmcgdGhlIGVmZmVjdCBhZ2FpbiBvbiB0aGUgbmV4dCByZW5kZXJcbiAgICAgICAgICAgIGRvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKCdSZW1vdmUgb3V0c2lkZSBjbGljayBoYW5kbGVyJyk7XG4gICAgICAgIH07XG4gICAgfSwgW2NhbGxiYWNrXSk7XG59XG4iXSwic291cmNlUm9vdCI6IiJ9