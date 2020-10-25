(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["TopNavUser"],{

/***/ "./browser/src/components/TopNavUser.jsx":
/*!***********************************************!*\
  !*** ./browser/src/components/TopNavUser.jsx ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return TopNavUser; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ui/Dropdown.jsx */ "./browser/src/components/ui/Dropdown.jsx");
/* harmony import */ var _User_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./User.jsx */ "./browser/src/components/User.jsx");



console.log('<TopNavUser> has been lazy loaded!');
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
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_ui_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php?do=logout"
  }, "Log out"))));
}

/***/ }),

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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1RvcE5hdlVzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvVXNlci5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvY29tcG9uZW50cy91aS9Ecm9wZG93bi5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvaG9va3MvdXNlLW91dHNpZGUtY2xpY2suanMiXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsIlRvcE5hdlVzZXIiLCJ1c2VybmFtZSIsIlVzZXIiLCJwcm9wcyIsImF2YXRhciIsImhyZWYiLCJ1bmRlZmluZWQiLCJ0YWciLCJ0YWdDaGlsZCIsIlJlYWN0IiwiY3JlYXRlRWxlbWVudCIsImNsYXNzTmFtZSIsImlzRHJvcGRvd25Ub2dnbGUiLCJtYXRjaENvbXBvbmVudCIsIkRyb3Bkb3duVG9nZ2xlIiwiaXNEcm9wZG93bk1lbnUiLCJEcm9wZG93bk1lbnUiLCJEcm9wZG93biIsImNsYXNzZXMiLCJjaGlsZHJlbiIsIm9wZW4iLCJzZXRPcGVuIiwidXNlU3RhdGUiLCJoYW5kbGVUb2dnbGUiLCJoYW5kbGVDbG9zZSIsInJlZiIsInVzZU91dHNpZGVDbGljayIsImNuIiwiZHJvcGRvd24iLCJDaGlsZHJlbiIsIm1hcCIsImNoaWxkIiwiaXNWYWxpZEVsZW1lbnQiLCJjbG9uZUVsZW1lbnQiLCJoYW5kbGVDbGljayIsImxpZ2h0IiwiRHJvcGRvd25JdGVtIiwiVG9nZ2xlIiwiTWVudSIsIkl0ZW0iLCJjYWxsYmFjayIsInVzZVJlZiIsImhhbmRsZUNsaWNrT3V0c2lkZSIsImV2ZW50IiwiY3VycmVudCIsImNvbnRhaW5zIiwidGFyZ2V0IiwidXNlRWZmZWN0IiwiZG9jdW1lbnQiLCJhZGRFdmVudExpc3RlbmVyIiwiY2xlYW51cCIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJ1c2VPdXRzaWRlQ2xpY2tfT0xEIl0sIm1hcHBpbmdzIjoiOzs7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QWtDZSxRQUFRLEdBQUcsTUFBTSxDQUFFLENBQXJELEVBQXVEO0FBQ25ELFdBQVNFLGtCQUFULENBQTRCQyxLQUE1QixFQUFtQztBQUFBOztBQUMvQjtBQUNBLFFBQUksbUJBQUNsQixHQUFHLENBQUNtQixPQUFMLDJFQUFDLGNBQWFDLFFBQWQsMERBQUMsMENBQXdCRixLQUFLLENBQUNHLE1BQTlCLENBQUQsQ0FBSixFQUE0QztBQUN4Q2hELGFBQU8sQ0FBQ0MsR0FBUixDQUFZLHdCQUFaLEVBQXNDeUMsUUFBdEM7QUFDQUEsY0FBUSxDQUFDRyxLQUFELENBQVI7QUFDSDtBQUNKOztBQUVEbEMsOENBQUssQ0FBQ3NDLFNBQU4sQ0FBZ0IsTUFBTTtBQUNsQjtBQUNBQyxZQUFRLENBQUNDLGdCQUFULENBQTBCLE9BQTFCLEVBQW1DUCxrQkFBbkM7QUFDQTVDLFdBQU8sQ0FBQ0MsR0FBUixDQUFZLGdDQUFaO0FBQ0EsV0FBTyxNQUFNO0FBQ1Q7QUFDQWlELGNBQVEsQ0FBQ0csbUJBQVQsQ0FBNkIsT0FBN0IsRUFBc0NULGtCQUF0QztBQUNBNUMsYUFBTyxDQUFDQyxHQUFSLENBQVksOEJBQVo7QUFDSCxLQUpEO0FBS0gsR0FURCxFQVNHLENBQUN5QyxRQUFELENBVEg7QUFVSCxDIiwiZmlsZSI6IlRvcE5hdlVzZXJfYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBEcm9wZG93biBmcm9tICcuL3VpL0Ryb3Bkb3duLmpzeCc7XG5pbXBvcnQgVXNlciBmcm9tICcuL1VzZXIuanN4JztcblxuY29uc29sZS5sb2coJzxUb3BOYXZVc2VyPiBoYXMgYmVlbiBsYXp5IGxvYWRlZCEnKTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVG9wTmF2VXNlcih7IHVzZXJuYW1lIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8RHJvcGRvd24gaWQ9XCJsb2dpbi11c2VyLWRyb3Bkb3duXCI+XG4gICAgICAgICAgICA8RHJvcGRvd24uVG9nZ2xlIGNsYXNzZXM9e3sgJ2J1dHRvbi1oZWFkZXInOiB0cnVlIH19PlxuICAgICAgICAgICAgICAgIDxVc2VyIHVzZXJuYW1lPXt1c2VybmFtZX0gaHJlZj1cIlwiIGF2YXRhcj1cIlwiIC8+XG4gICAgICAgICAgICA8L0Ryb3Bkb3duLlRvZ2dsZT5cbiAgICAgICAgICAgIDxEcm9wZG93bi5NZW51PlxuICAgICAgICAgICAgICAgIDxEcm9wZG93bi5JdGVtPjxhIGhyZWY9e2AvfiR7dXNlcm5hbWV9YH0+UHJvZmlsZTwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX0vZ2FtZXNgfT5HYW1lczwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj1cIi9sb2dpbi5waHA/ZG89bG9nb3V0XCI+TG9nIG91dDwvYT48L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICA8L0Ryb3Bkb3duLk1lbnU+XG4gICAgICAgIDwvRHJvcGRvd24+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIFVzZXIocHJvcHMpIHtcbiAgICBsZXQgeyB1c2VybmFtZSwgYXZhdGFyLCBocmVmIH0gPSBwcm9wcztcblxuICAgIGlmIChocmVmID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgaHJlZiA9IGB+JHt1c2VybmFtZX1gO1xuICAgIH1cblxuICAgIGxldCB0YWc7XG4gICAgY29uc3QgdGFnQ2hpbGQgPSA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyLXVzZXJuYW1lXCI+e3VzZXJuYW1lfTwvc3Bhbj47XG4gICAgaWYgKGhyZWYpIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnYScsIHsgaHJlZiwgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgdGFnID0gUmVhY3QuY3JlYXRlRWxlbWVudCgnc3BhbicsIHsgY2xhc3NOYW1lOiAndXNlci1saW5rJyB9LCB0YWdDaGlsZCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidXNlclwiPlxuICAgICAgICAgICAge3RhZ31cbiAgICAgICAgPC9zcGFuPlxuICAgICk7XG59XG4iLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IGNuIGZyb20gJ2NsYXNzbmFtZXMnO1xuaW1wb3J0IG1hdGNoQ29tcG9uZW50IGZyb20gJy4uLy4uL2xpYi9tYXRjaC1jb21wb25lbnQuanMnO1xuaW1wb3J0IHVzZU91dHNpZGVDbGljayBmcm9tICcuLi8uLi9ob29rcy91c2Utb3V0c2lkZS1jbGljay5qcyc7XG5cbmNvbnN0IGlzRHJvcGRvd25Ub2dnbGUgPSBtYXRjaENvbXBvbmVudChEcm9wZG93blRvZ2dsZSk7XG5jb25zdCBpc0Ryb3Bkb3duTWVudSA9IG1hdGNoQ29tcG9uZW50KERyb3Bkb3duTWVudSk7XG5cbmZ1bmN0aW9uIERyb3Bkb3duKHsgY2xhc3NlcywgY2hpbGRyZW4sIC4uLnByb3BzIH0pIHtcbiAgICBjb25zdCBbb3Blbiwgc2V0T3Blbl0gPSBSZWFjdC51c2VTdGF0ZShmYWxzZSk7XG4gICAgY29uc3QgaGFuZGxlVG9nZ2xlID0gKCkgPT4gc2V0T3Blbighb3Blbik7XG4gICAgY29uc3QgaGFuZGxlQ2xvc2UgPSAoKSA9PiBzZXRPcGVuKGZhbHNlKTtcblxuICAgIGNvbnN0IHJlZiA9IHVzZU91dHNpZGVDbGljayhoYW5kbGVDbG9zZSk7XG5cbiAgICBjb25zdCBjbGFzc05hbWUgPSBjbih7XG4gICAgICAgIC4uLmNsYXNzZXMsXG4gICAgICAgIGRyb3Bkb3duOiB0cnVlLFxuICAgICAgICBvcGVuLFxuICAgIH0pO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiByZWY9e3JlZn0gY2xhc3NOYW1lPXtjbGFzc05hbWV9IHsuLi5wcm9wc30+XG4gICAgICAgICAgICB7LyogTWFwIGNoaWxkcmVuICYgaW5qZWN0IGxpc3RlbmVycyAqL31cbiAgICAgICAgICAgIHtSZWFjdC5DaGlsZHJlbi5tYXAoY2hpbGRyZW4sIChjaGlsZCkgPT4ge1xuICAgICAgICAgICAgICAgIGlmICghUmVhY3QuaXNWYWxpZEVsZW1lbnQoY2hpbGQpKSB7XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBjaGlsZDtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAvLyBCdXR0b24gdG9nZ2xlIGRyb3Bkb3duIG1lbnVcbiAgICAgICAgICAgICAgICBpZiAoaXNEcm9wZG93blRvZ2dsZShjaGlsZCkpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNsb25lRWxlbWVudChjaGlsZCwge1xuICAgICAgICAgICAgICAgICAgICAgICAgaGFuZGxlQ2xpY2s6IGhhbmRsZVRvZ2dsZSxcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGNoaWxkO1xuICAgICAgICAgICAgfSl9XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duVG9nZ2xlKHsgY2xhc3NlcywgY2hpbGRyZW4sIGhhbmRsZUNsaWNrIH0pIHtcbiAgICBjb25zdCBjbGFzc05hbWUgPSBjbih7XG4gICAgICAgIC4uLmNsYXNzZXMsXG4gICAgICAgICdkcm9wZG93bi10b2dnbGUnOiB0cnVlLFxuICAgIH0pO1xuXG4gICAgcmV0dXJuIChcbiAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3NOYW1lPXtjbGFzc05hbWV9IG9uQ2xpY2s9e2hhbmRsZUNsaWNrfSBhcmlhLWhhc3BvcHVwPVwidHJ1ZVwiIGFyaWEtZXhwYW5kZWQ9XCJ0cnVlXCI+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvYnV0dG9uPlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duTWVudSh7IGNsYXNzZXMsIGNoaWxkcmVuIH0pIHtcbiAgICBjb25zdCBjbGFzc05hbWUgPSBjbih7XG4gICAgICAgIC4uLmNsYXNzZXMsXG4gICAgICAgICdkcm9wZG93bi1tZW51JzogdHJ1ZSxcbiAgICAgICAgbGlnaHQ6IHRydWUsXG4gICAgfSk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT17Y2xhc3NOYW1lfSByb2xlPVwibWVudVwiPlxuICAgICAgICAgICAge2NoaWxkcmVufVxuICAgICAgICA8L2Rpdj5cbiAgICApO1xufVxuXG5mdW5jdGlvbiBEcm9wZG93bkl0ZW0oeyBjaGlsZHJlbiB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJkcm9wZG93bi1pdGVtXCIgcm9sZT1cIm1lbnVpdGVtXCI+e2NoaWxkcmVufTwvZGl2PlxuICAgICk7XG59XG5cbkRyb3Bkb3duLlRvZ2dsZSA9IERyb3Bkb3duVG9nZ2xlO1xuRHJvcGRvd24uTWVudSA9IERyb3Bkb3duTWVudTtcbkRyb3Bkb3duLkl0ZW0gPSBEcm9wZG93bkl0ZW07XG5cbmV4cG9ydCBkZWZhdWx0IERyb3Bkb3duO1xuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuLyoqXG4gKiBIb29rIHRoYXQgbGlzdGVucyBmb3IgY2xpY2tzIG91dHNpZGUgb2YgYSByZWZlcmVuY2UuXG4gKlxuICogQHBhcmFtIHtGdW5jdGlvbn0gY2FsbGJhY2sgRnVuY3Rpb24gdG8gZXhlY3V0ZSBvbiBzdWNjZXNzZnVsIG91dHNpZGUgY2xpY2tcbiAqXG4gKiBAcmV0dXJucyB7UmVmZXJlbmNlfSBSZWZlcmVuY2UgdG8gXCJpbnNpZGVcIiBlbGVtZW50XG4gKi9cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIHVzZU91dHNpZGVDbGljayhjYWxsYmFjaykge1xuICAgIGNvbnN0IHJlZiA9IFJlYWN0LnVzZVJlZigpO1xuXG4gICAgZnVuY3Rpb24gaGFuZGxlQ2xpY2tPdXRzaWRlKGV2ZW50KSB7XG4gICAgICAgIC8vIElmIHRoZSBjbGljayBpcyByZWdpc3RlcmVkIG91dHNpZGUgdGhlIGdpdmVuIHJlZiwgdHJpZ2dlciBjYlxuICAgICAgICBpZiAoIXJlZi5jdXJyZW50Py5jb250YWlucz8uKGV2ZW50LnRhcmdldCkpIHtcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKCdPdXRzaWRlIGNsaWNrIGRldGVjdGVkJywgY2FsbGJhY2spO1xuICAgICAgICAgICAgY2FsbGJhY2soZXZlbnQpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgUmVhY3QudXNlRWZmZWN0KCgpID0+IHtcbiAgICAgICAgLy8gQmluZCB0aGUgZXZlbnQgbGlzdGVuZXJcbiAgICAgICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBoYW5kbGVDbGlja091dHNpZGUpO1xuICAgICAgICBjb25zb2xlLmxvZygnTGlzdGVuaW5nIGZvciBvdXRzaWRlIGNsaWNrLi4uJyk7XG4gICAgICAgIHJldHVybiBmdW5jdGlvbiBjbGVhbnVwKCkge1xuICAgICAgICAgICAgLy8gQ2xlYW4gdXAgYmVmb3JlIGNhbGxpbmcgdGhlIGVmZmVjdCBhZ2FpbiBvbiB0aGUgbmV4dCByZW5kZXJcbiAgICAgICAgICAgIGRvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKCdSZW1vdmUgb3V0c2lkZSBjbGljayBoYW5kbGVyJyk7XG4gICAgICAgIH07XG4gICAgfSwgW3JlZl0pO1xuXG4gICAgcmV0dXJuIHJlZjtcbn1cblxuZnVuY3Rpb24gdXNlT3V0c2lkZUNsaWNrX09MRChyZWYsIGNhbGxiYWNrID0gKCkgPT4ge30pIHtcbiAgICBmdW5jdGlvbiBoYW5kbGVDbGlja091dHNpZGUoZXZlbnQpIHtcbiAgICAgICAgLy8gSWYgdGhlIGNsaWNrIGlzIHJlZ2lzdGVyZWQgb3V0c2lkZSB0aGUgZ2l2ZW4gcmVmLCB0cmlnZ2VyIGNiXG4gICAgICAgIGlmICghcmVmLmN1cnJlbnQ/LmNvbnRhaW5zPy4oZXZlbnQudGFyZ2V0KSkge1xuICAgICAgICAgICAgY29uc29sZS5sb2coJ091dHNpZGUgY2xpY2sgZGV0ZWN0ZWQnLCBjYWxsYmFjayk7XG4gICAgICAgICAgICBjYWxsYmFjayhldmVudCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBSZWFjdC51c2VFZmZlY3QoKCkgPT4ge1xuICAgICAgICAvLyBCaW5kIHRoZSBldmVudCBsaXN0ZW5lclxuICAgICAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgIGNvbnNvbGUubG9nKCdMaXN0ZW5pbmcgZm9yIG91dHNpZGUgY2xpY2suLi4nKTtcbiAgICAgICAgcmV0dXJuICgpID0+IHtcbiAgICAgICAgICAgIC8vIENsZWFuIHVwIGJlZm9yZSBjYWxsaW5nIHRoZSBlZmZlY3QgYWdhaW4gb24gdGhlIG5leHQgcmVuZGVyXG4gICAgICAgICAgICBkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKCdjbGljaycsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgICAgICBjb25zb2xlLmxvZygnUmVtb3ZlIG91dHNpZGUgY2xpY2sgaGFuZGxlcicpO1xuICAgICAgICB9O1xuICAgIH0sIFtjYWxsYmFja10pO1xufVxuIl0sInNvdXJjZVJvb3QiOiIifQ==