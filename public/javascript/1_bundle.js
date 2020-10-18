(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[1],{

/***/ "./browser/src/components/Dropdown.jsx":
/*!*********************************************!*\
  !*** ./browser/src/components/Dropdown.jsx ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../lib/match-component.js */ "./browser/src/lib/match-component.js");
/* harmony import */ var _lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../lib/use-outside-click.js */ "./browser/src/lib/use-outside-click.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isDropdownToggle = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownToggle);
const isDropdownMenu = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownMenu);

function Dropdown(props) {
  const {
    className,
    children,
    ...rest
  } = props;
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleToggle = () => setOpen(!open);

  const handleClose = () => setOpen(false);

  const classnames = classnames__WEBPACK_IMPORTED_MODULE_1___default()({
    className,
    dropdown: true,
    open
  }); // Event listener is always active... problem?

  const wrapperRef = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef(null);
  Object(_lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__["default"])(wrapperRef, handleClose);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    ref: wrapperRef,
    className: classnames
  }, rest), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, child => {
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
  className,
  children,
  handleClick
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    className: `dropdown-toggle ${className}`,
    type: "button",
    onClick: handleClick,
    "aria-haspopup": "true",
    "aria-expanded": "true"
  }, children);
}

function DropdownMenu({
  className,
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: `dropdown-menu light ${className}`,
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

/***/ "./browser/src/components/NavMenuUser.jsx":
/*!************************************************!*\
  !*** ./browser/src/components/NavMenuUser.jsx ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return NavMenuUser; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dropdown.jsx */ "./browser/src/components/Dropdown.jsx");
/* harmony import */ var _User_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./User.jsx */ "./browser/src/components/User.jsx");



console.log('<NavMenuUser> has been lazy loaded!');
function NavMenuUser({
  username
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: "login-user-dropdown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Toggle, {
    className: "access-button"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_User_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    username: username,
    href: "",
    avatar: ""
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Menu, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, "foo"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}`
  }, "Profile")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}/games`
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
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

/***/ "./browser/src/lib/match-component.js":
/*!********************************************!*\
  !*** ./browser/src/lib/match-component.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
const matchComponent = Component => c => {
  // React Component
  if (c.type === Component) {
    return true;
  } // Matching componentType


  if (c.props && c.props.componentType === Component) {
    return true;
  }

  return false;
};

/* harmony default export */ __webpack_exports__["default"] = (matchComponent);

/***/ }),

/***/ "./browser/src/lib/use-outside-click.js":
/*!**********************************************!*\
  !*** ./browser/src/lib/use-outside-click.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useOutsideClick; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Hook that alerts clicks outside of the passed ref
 */

function useOutsideClick(ref, callback) {
  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    /**
     * Alert if clicked on outside of element
     */
    function handleClickOutside(event) {
      if (ref.current && !ref.current.contains(event.target)) {
        console.log('outside click detected', callback);
        callback();
      }
    } // Bind the event listener


    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [ref]);
}
/**
 * Example use:
 * Component that alerts if you click outside of it
 */

function MyComponent(props) {
  const wrapperRef = useRef(null);
  useOutsideClick(wrapperRef);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    ref: wrapperRef
  }, props.children);
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0Ryb3Bkb3duLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL05hdk1lbnVVc2VyLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1VzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2xpYi9tYXRjaC1jb21wb25lbnQuanMiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvbGliL3VzZS1vdXRzaWRlLWNsaWNrLmpzIl0sIm5hbWVzIjpbImlzRHJvcGRvd25Ub2dnbGUiLCJtYXRjaENvbXBvbmVudCIsIkRyb3Bkb3duVG9nZ2xlIiwiaXNEcm9wZG93bk1lbnUiLCJEcm9wZG93bk1lbnUiLCJEcm9wZG93biIsInByb3BzIiwiY2xhc3NOYW1lIiwiY2hpbGRyZW4iLCJyZXN0Iiwib3BlbiIsInNldE9wZW4iLCJSZWFjdCIsInVzZVN0YXRlIiwiaGFuZGxlVG9nZ2xlIiwiaGFuZGxlQ2xvc2UiLCJjbGFzc25hbWVzIiwiY2xhc3NOYW1lcyIsImRyb3Bkb3duIiwid3JhcHBlclJlZiIsInVzZVJlZiIsInVzZU91dHNpZGVDbGljayIsIkNoaWxkcmVuIiwibWFwIiwiY2hpbGQiLCJpc1ZhbGlkRWxlbWVudCIsImNsb25lRWxlbWVudCIsImhhbmRsZUNsaWNrIiwiRHJvcGRvd25JdGVtIiwiVG9nZ2xlIiwiTWVudSIsIkl0ZW0iLCJjb25zb2xlIiwibG9nIiwiTmF2TWVudVVzZXIiLCJ1c2VybmFtZSIsIlVzZXIiLCJhdmF0YXIiLCJocmVmIiwidW5kZWZpbmVkIiwidGFnIiwidGFnQ2hpbGQiLCJjcmVhdGVFbGVtZW50IiwiQ29tcG9uZW50IiwiYyIsInR5cGUiLCJjb21wb25lbnRUeXBlIiwicmVmIiwiY2FsbGJhY2siLCJ1c2VFZmZlY3QiLCJoYW5kbGVDbGlja091dHNpZGUiLCJldmVudCIsImN1cnJlbnQiLCJjb250YWlucyIsInRhcmdldCIsImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0ZW5lciIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJNeUNvbXBvbmVudCJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBRUEsTUFBTUEsZ0JBQWdCLEdBQUdDLHVFQUFjLENBQUNDLGNBQUQsQ0FBdkM7QUFDQSxNQUFNQyxjQUFjLEdBQUdGLHVFQUFjLENBQUNHLFlBQUQsQ0FBckM7O0FBRUEsU0FBU0MsUUFBVCxDQUFrQkMsS0FBbEIsRUFBeUI7QUFDckIsUUFBTTtBQUFFQyxhQUFGO0FBQWFDLFlBQWI7QUFBdUIsT0FBR0M7QUFBMUIsTUFBbUNILEtBQXpDO0FBRUEsUUFBTSxDQUFDSSxJQUFELEVBQU9DLE9BQVAsSUFBa0JDLDRDQUFLLENBQUNDLFFBQU4sQ0FBZSxLQUFmLENBQXhCOztBQUNBLFFBQU1DLFlBQVksR0FBRyxNQUFNSCxPQUFPLENBQUMsQ0FBQ0QsSUFBRixDQUFsQzs7QUFDQSxRQUFNSyxXQUFXLEdBQUcsTUFBTUosT0FBTyxDQUFDLEtBQUQsQ0FBakM7O0FBRUEsUUFBTUssVUFBVSxHQUFHQyxpREFBVSxDQUFDO0FBQzFCVixhQUQwQjtBQUUxQlcsWUFBUSxFQUFFLElBRmdCO0FBRzFCUjtBQUgwQixHQUFELENBQTdCLENBUHFCLENBYXJCOztBQUNBLFFBQU1TLFVBQVUsR0FBR1AsNENBQUssQ0FBQ1EsTUFBTixDQUFhLElBQWIsQ0FBbkI7QUFDQUMsMkVBQWUsQ0FBQ0YsVUFBRCxFQUFhSixXQUFiLENBQWY7QUFFQSxzQkFDSTtBQUFLLE9BQUcsRUFBRUksVUFBVjtBQUFzQixhQUFTLEVBQUVIO0FBQWpDLEtBQWlEUCxJQUFqRCxHQUVLRyw0Q0FBSyxDQUFDVSxRQUFOLENBQWVDLEdBQWYsQ0FBbUJmLFFBQW5CLEVBQThCZ0IsS0FBRCxJQUFXO0FBQ3JDLFFBQUksZUFBQ1osNENBQUssQ0FBQ2EsY0FBTixDQUFxQkQsS0FBckIsQ0FBTCxFQUFrQztBQUM5QixhQUFPQSxL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QUNDLGdCQUFULENBQTBCLFdBQTFCLEVBQXVDTixrQkFBdkM7QUFDQSxXQUFPLE1BQU07QUFDVDtBQUNBSyxjQUFRLENBQUNFLG1CQUFULENBQTZCLFdBQTdCLEVBQTBDUCxrQkFBMUM7QUFDSCxLQUhEO0FBSUgsR0FqQkQsRUFpQkcsQ0FBQ0gsR0FBRCxDQWpCSDtBQWtCSDtBQUVEOzs7OztBQUlBLFNBQVNXLFdBQVQsQ0FBcUJwRCxLQUFyQixFQUE0QjtBQUN4QixRQUFNYSxVQUFVLEdBQUdDLE1BQU0sQ0FBQyxJQUFELENBQXpCO0FBQ0FDLGlCQUFlLENBQUNGLFVBQUQsQ0FBZjtBQUVBLHNCQUFPO0FBQUssT0FBRyxFQUFFQTtBQUFWLEtBQXVCYixLQUFLLENBQUNFLFFBQTdCLENBQVA7QUFDSCxDIiwiZmlsZSI6IjFfYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBjbGFzc05hbWVzIGZyb20gJ2NsYXNzbmFtZXMnO1xuaW1wb3J0IG1hdGNoQ29tcG9uZW50IGZyb20gJy4uL2xpYi9tYXRjaC1jb21wb25lbnQuanMnO1xuaW1wb3J0IHVzZU91dHNpZGVDbGljayBmcm9tICcuLi9saWIvdXNlLW91dHNpZGUtY2xpY2suanMnO1xuXG5jb25zdCBpc0Ryb3Bkb3duVG9nZ2xlID0gbWF0Y2hDb21wb25lbnQoRHJvcGRvd25Ub2dnbGUpO1xuY29uc3QgaXNEcm9wZG93bk1lbnUgPSBtYXRjaENvbXBvbmVudChEcm9wZG93bk1lbnUpO1xuXG5mdW5jdGlvbiBEcm9wZG93bihwcm9wcykge1xuICAgIGNvbnN0IHsgY2xhc3NOYW1lLCBjaGlsZHJlbiwgLi4ucmVzdCB9ID0gcHJvcHM7XG5cbiAgICBjb25zdCBbb3Blbiwgc2V0T3Blbl0gPSBSZWFjdC51c2VTdGF0ZShmYWxzZSk7XG4gICAgY29uc3QgaGFuZGxlVG9nZ2xlID0gKCkgPT4gc2V0T3Blbighb3Blbik7XG4gICAgY29uc3QgaGFuZGxlQ2xvc2UgPSAoKSA9PiBzZXRPcGVuKGZhbHNlKTtcblxuICAgIGNvbnN0IGNsYXNzbmFtZXMgPSBjbGFzc05hbWVzKHtcbiAgICAgICAgY2xhc3NOYW1lLFxuICAgICAgICBkcm9wZG93bjogdHJ1ZSxcbiAgICAgICAgb3BlbixcbiAgICB9KTtcblxuICAgIC8vIEV2ZW50IGxpc3RlbmVyIGlzIGFsd2F5cyBhY3RpdmUuLi4gcHJvYmxlbT9cbiAgICBjb25zdCB3cmFwcGVyUmVmID0gUmVhY3QudXNlUmVmKG51bGwpO1xuICAgIHVzZU91dHNpZGVDbGljayh3cmFwcGVyUmVmLCBoYW5kbGVDbG9zZSk7XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IHJlZj17d3JhcHBlclJlZn0gY2xhc3NOYW1lPXtjbGFzc25hbWVzfSB7Li4ucmVzdH0+XG4gICAgICAgICAgICB7LyogTWFwIGNoaWxkcmVuICYgaW5qZWN0IGxpc3RlbmVycyAqL31cbiAgICAgICAgICAgIHtSZWFjdC5DaGlsZHJlbi5tYXAoY2hpbGRyZW4sIChjaGlsZCkgPT4ge1xuICAgICAgICAgICAgICAgIGlmICghUmVhY3QuaXNWYWxpZEVsZW1lbnQoY2hpbGQpKSB7XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBjaGlsZDtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAvLyBCdXR0b24gdG9nZ2xlIGRyb3Bkb3duIG1lbnVcbiAgICAgICAgICAgICAgICBpZiAoaXNEcm9wZG93blRvZ2dsZShjaGlsZCkpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIFJlYWN0LmNsb25lRWxlbWVudChjaGlsZCwge1xuICAgICAgICAgICAgICAgICAgICAgICAgaGFuZGxlQ2xpY2s6IGhhbmRsZVRvZ2dsZSxcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGNoaWxkO1xuICAgICAgICAgICAgfSl9XG4gICAgICAgIDwvZGl2PlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duVG9nZ2xlKHsgY2xhc3NOYW1lLCBjaGlsZHJlbiwgaGFuZGxlQ2xpY2sgfSkge1xuICAgIHJldHVybiAoXG4gICAgICAgIDxidXR0b24gY2xhc3NOYW1lPXtgZHJvcGRvd24tdG9nZ2xlICR7Y2xhc3NOYW1lfWB9IHR5cGU9XCJidXR0b25cIiBvbkNsaWNrPXtoYW5kbGVDbGlja30gYXJpYS1oYXNwb3B1cD1cInRydWVcIiBhcmlhLWV4cGFuZGVkPVwidHJ1ZVwiPlxuICAgICAgICAgICAge2NoaWxkcmVufVxuICAgICAgICA8L2J1dHRvbj5cbiAgICApO1xufVxuXG5mdW5jdGlvbiBEcm9wZG93bk1lbnUoeyBjbGFzc05hbWUsIGNoaWxkcmVuIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT17YGRyb3Bkb3duLW1lbnUgbGlnaHQgJHtjbGFzc05hbWV9YH0gcm9sZT1cIm1lbnVcIj5cbiAgICAgICAgICAgIHtjaGlsZHJlbn1cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25JdGVtKHsgY2hpbGRyZW4gfSkge1xuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiZHJvcGRvd24taXRlbVwiIHJvbGU9XCJtZW51aXRlbVwiPntjaGlsZHJlbn08L2Rpdj5cbiAgICApO1xufVxuXG5Ecm9wZG93bi5Ub2dnbGUgPSBEcm9wZG93blRvZ2dsZTtcbkRyb3Bkb3duLk1lbnUgPSBEcm9wZG93bk1lbnU7XG5Ecm9wZG93bi5JdGVtID0gRHJvcGRvd25JdGVtO1xuXG5leHBvcnQgZGVmYXVsdCBEcm9wZG93bjtcbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgRHJvcGRvd24gZnJvbSAnLi9Ecm9wZG93bi5qc3gnO1xuaW1wb3J0IFVzZXIgZnJvbSAnLi9Vc2VyLmpzeCc7XG5cbmNvbnNvbGUubG9nKCc8TmF2TWVudVVzZXI+IGhhcyBiZWVuIGxhenkgbG9hZGVkIScpO1xuXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBOYXZNZW51VXNlcih7IHVzZXJuYW1lIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8RHJvcGRvd24gaWQ9XCJsb2dpbi11c2VyLWRyb3Bkb3duXCI+XG4gICAgICAgICAgICA8RHJvcGRvd24uVG9nZ2xlIGNsYXNzTmFtZT1cImFjY2Vzcy1idXR0b25cIj5cbiAgICAgICAgICAgICAgICA8VXNlciB1c2VybmFtZT17dXNlcm5hbWV9IGhyZWY9XCJcIiBhdmF0YXI9XCJcIiAvPlxuICAgICAgICAgICAgPC9Ecm9wZG93bi5Ub2dnbGU+XG4gICAgICAgICAgICA8RHJvcGRvd24uTWVudT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT5mb288L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX1gfT5Qcm9maWxlPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPXtgL34ke3VzZXJuYW1lfS9nYW1lc2B9PkdhbWVzPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPVwiL2xvZ2luLnBocD9kbz1sb2dvdXRcIj5Mb2cgb3V0PC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgIDwvRHJvcGRvd24uTWVudT5cbiAgICAgICAgPC9Ecm9wZG93bj5cbiAgICApO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVXNlcihwcm9wcykge1xuICAgIGxldCB7IHVzZXJuYW1lLCBhdmF0YXIsIGhyZWYgfSA9IHByb3BzO1xuXG4gICAgaWYgKGhyZWYgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICBocmVmID0gYH4ke3VzZXJuYW1lfWA7XG4gICAgfVxuXG4gICAgbGV0IHRhZztcbiAgICBjb25zdCB0YWdDaGlsZCA9IDxzcGFuIGNsYXNzTmFtZT1cInVzZXItdXNlcm5hbWVcIj57dXNlcm5hbWV9PC9zcGFuPjtcbiAgICBpZiAoaHJlZikge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdhJywgeyBocmVmLCBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdzcGFuJywgeyBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyXCI+XG4gICAgICAgICAgICB7dGFnfVxuICAgICAgICA8L3NwYW4+XG4gICAgKTtcbn1cbiIsImNvbnN0IG1hdGNoQ29tcG9uZW50ID0gKENvbXBvbmVudCkgPT4gKGMpID0+IHtcclxuICAgIC8vIFJlYWN0IENvbXBvbmVudFxyXG4gICAgaWYgKGMudHlwZSA9PT0gQ29tcG9uZW50KSB7XHJcbiAgICAgICAgcmV0dXJuIHRydWU7XHJcbiAgICB9XHJcblxyXG4gICAgLy8gTWF0Y2hpbmcgY29tcG9uZW50VHlwZVxyXG4gICAgaWYgKGMucHJvcHMgJiYgYy5wcm9wcy5jb21wb25lbnRUeXBlID09PSBDb21wb25lbnQpIHtcclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH1cclxuXHJcbiAgICByZXR1cm4gZmFsc2U7XHJcbn07XHJcblxyXG5leHBvcnQgZGVmYXVsdCBtYXRjaENvbXBvbmVudDsiLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuXG4vKipcbiAqIEhvb2sgdGhhdCBhbGVydHMgY2xpY2tzIG91dHNpZGUgb2YgdGhlIHBhc3NlZCByZWZcbiAqL1xuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gdXNlT3V0c2lkZUNsaWNrKHJlZiwgY2FsbGJhY2spIHtcbiAgICBSZWFjdC51c2VFZmZlY3QoKCkgPT4ge1xuICAgICAgICAvKipcbiAgICAgICAgICogQWxlcnQgaWYgY2xpY2tlZCBvbiBvdXRzaWRlIG9mIGVsZW1lbnRcbiAgICAgICAgICovXG4gICAgICAgIGZ1bmN0aW9uIGhhbmRsZUNsaWNrT3V0c2lkZShldmVudCkge1xuICAgICAgICAgICAgaWYgKHJlZi5jdXJyZW50ICYmICFyZWYuY3VycmVudC5jb250YWlucyhldmVudC50YXJnZXQpKSB7XG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2coJ291dHNpZGUgY2xpY2sgZGV0ZWN0ZWQnLCBjYWxsYmFjayk7XG4gICAgICAgICAgICAgICAgY2FsbGJhY2soKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIEJpbmQgdGhlIGV2ZW50IGxpc3RlbmVyXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ21vdXNlZG93bicsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgIHJldHVybiAoKSA9PiB7XG4gICAgICAgICAgICAvLyBDbGVhbiB1cCBiZWZvcmUgY2FsbGluZyB0aGUgZWZmZWN0IGFnYWluIG9uIHRoZSBuZXh0IHJlbmRlclxuICAgICAgICAgICAgZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcignbW91c2Vkb3duJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgfTtcbiAgICB9LCBbcmVmXSk7XG59XG5cbi8qKlxuICogRXhhbXBsZSB1c2U6XG4gKiBDb21wb25lbnQgdGhhdCBhbGVydHMgaWYgeW91IGNsaWNrIG91dHNpZGUgb2YgaXRcbiAqL1xuZnVuY3Rpb24gTXlDb21wb25lbnQocHJvcHMpIHtcbiAgICBjb25zdCB3cmFwcGVyUmVmID0gdXNlUmVmKG51bGwpO1xuICAgIHVzZU91dHNpZGVDbGljayh3cmFwcGVyUmVmKTtcblxuICAgIHJldHVybiA8ZGl2IHJlZj17d3JhcHBlclJlZn0+e3Byb3BzLmNoaWxkcmVufTwvZGl2Pjtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=