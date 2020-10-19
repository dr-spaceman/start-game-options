// Entry point for React components on all pages

import React from 'react';
import ReactDOM from 'react-dom';

// Stylesheets that get injected into <head>
import 'normalize.css';
import '../styles/app.scss';

// Components to render
import Colophon from './components/Colophon.jsx';
import Header from './components/Header.jsx';
import Test from './components/Test.jsx';

// Grab data-* properties from <header> element and pass them as props to <Header> component
const headerElement = document.getElementById('header');
ReactDOM.render(React.createElement(Header, {...headerElement.dataset}), headerElement);

ReactDOM.render(
    React.createElement(Colophon),
    document.getElementById('colophon'),
);

const Content = () => {
    const [open, setOpen] = React.useState(true);

    return (
        <>
            <h1>Hello World!</h1>
            {open &&
                <>
                    <p>Lorem ipsum dolor sit amet <a href="#consectetur">consectetur adipisicing elit</a>. Eveniet voluptas incidunt atque ipsam, nobis quis inventore, velit libero vel autem tempora, fugit soluta excepturi <a href="#foo">voluptatum</a>! Soluta possimus nihil dolore hic.</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, repellendus ullam cumque sequi deserunt cum possimus, deleniti impedit pariatur atque eligendi. Eius debitis delectus maxime esse a, odio sint mollitia!</p>
                    <p><a href="#bar">Lorem ipsum dolor sit amet consectetur</a>, adipisicing elit. Quis tenetur facilis ipsum doloremque magni cum. Praesentium reiciendis vitae omnis ex sint eaque eos necessitatibus assumenda atque reprehenderit, commodi quod. Nam! Lorem ipsum dolor sit amet consectetur adipisicing elit. Obcaecati consectetur similique nulla veritatis a impedit provident eaque dignissimos facere soluta voluptate ab aliquam quidem culpa dolores hic excepturi, eius quae?</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias facere magni culpa molestiae voluptates ducimus? Ducimus minus nesciunt tempora ad asperiores! Totam autem dolore eos delectus reprehenderit ipsa animi omnis.</p>
                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. <a href="#baz">Itaque beatae</a> eaque praesentium modi voluptates libero obcaecati earum? Officia impedit distinctio deleniti exercitationem delectus! Assumenda, hic a eaque nobis velit quis.</p>
                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusamus magni ut aliquam officiis nostrum consequatur tempore, at repudiandae, laudantium exercitationem itaque cum, et voluptate suscipit modi unde ad doloremque sit! Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem maiores quisquam distinctio quos qui adipisci voluptates perferendis officia commodi, fugit eius est ut corrupti reprehenderit fuga quibusdam, cum itaque sequi?</p>
                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam deserunt ea natus iusto ipsa, labore in consectetur, beatae commodi voluptas hic, ratione asperiores dicta accusantium optio quas unde omnis error!</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa maxime quod ex iure eius et, sint doloremque! Libero exercitationem pariatur hic dignissimos, dolorum consequuntur odio consectetur voluptate accusamus voluptatem a.</p>
                    <p>Fin.</p>
                    <h1>&gt;START GAME Options Foo___foo.bar<br/>____________ (Layout font)</h1>
                    <h1 style={{ fontFamily: 'Press Start' }}>&gt;START GAME Options Foo___foo.bar<br/>____________ (Monospace font)</h1>
                    <h1 style={{ fontFamily: 'Emulogic' }}>&gt;START GAME Options Foo___foo.bar<br />____________</h1>
                    <h1 style={{ fontFamily: 'Yoster Island' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'Bc.BMP07_A' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'Bc.BMP07_K' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'NineteenNinetySeven' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'Barcade Brawl' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'Barcade Brawl' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                    <h1 style={{ fontFamily: 'Super Legend Boy' }}>&gt;START GAME Options Foo___foo.bar<br/>____________</h1>
                </>}
            <button type="button" onClick={() => setOpen(!open)}>Toggle filler text</button>
            <p>Env</p>
            {/* WARNING: These variables will be exposed in the bundle */}
            <ul>
                <li>ENVIRONMENT: {process.env.ENVIRONMENT}</li>
                <li>HOST_DOMAIN: {process.env.HOST_DOMAIN}</li>
            </ul>
            <h2>Testing</h2>
            <Test />
        </>
    );
};

ReactDOM.render(React.createElement(Content), document.getElementById('content'));

// Demonstrates lazy loading files

function printComponent() {
    const element = document.createElement('div');
    const button = document.createElement('button');
    const br = document.createElement('br');

    button.innerHTML = 'Click me and look at the console! But not before I lazy load a js component...';
    element.appendChild(br);
    element.appendChild(button);

    // Note that because a network request is involved, some indication
    // of loading would need to be shown in a production-level site/app.
    button.onclick = e => import(/* webpackChunkName: "print" */ './lib/print').then(module => {
        // Note that when using import() on ES6 modules you must reference the .default property as it's the actual module object that will be returned when the promise is resolved.
        const print = module.default;

        print();
    });

    return element;
}

document.getElementById('content').appendChild(printComponent());

// Router

// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );

// ReactDOM.render(element, document.getElementById('root'));
