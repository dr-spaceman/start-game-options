import React from 'react';
import ReactDOM from 'react-dom';

// Components to render
import Colophon from './Colophon.jsx';
import Header from './Header.jsx';

// Stylesheets that get injected into <head>
import 'normalize.css';
import '../styles/app.scss';

// Grab data-* properties from <header> element and pass them as props to <Header> component
const headerElement = document.getElementById('header');
ReactDOM.render(<Header {...headerElement.dataset} />, headerElement);

ReactDOM.render(
    React.createElement(Colophon),
    document.getElementById('colophon'),
);

// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );

// ReactDOM.render(element, document.getElementById('root'));

// Hot Module Replacement
if (module.hot) {
    module.hot.accept();
}
