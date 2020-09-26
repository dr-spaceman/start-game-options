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
            </>}
            <button type="button" onClick={() => setOpen(!open)}>Toggle filler text</button>
        </>
    );
};

ReactDOM.render(<Content />, document.getElementById('content'));

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
