import React from 'react';
import NavMenu from './NavMenu.jsx';
import Search from './Search.jsx';
import { QuestionBlock, LoadingMascot } from '../lib/icons.js';

const NavMenuUser = React.lazy(() => import(/* webpackChunkName: "NavMenuUser" */'./NavMenuUser.jsx'));
const Login = React.lazy(() => import(/* webpackChunkName: "Login" */'./Login.jsx'));

function User({ username }) {
    const [state, setState] = React.useState(false);
    const lazyloadLogin = (event) => {
        event.preventDefault();
        setState(true);
    }
    const LoginButton = () => (
        <a href="/login.php" title="Login" onClick={lazyloadLogin} className="user user-unknown">
            <QuestionBlock className="user-avatar thumbnail" />
            <span className="user-username">Login</span>
        </a>
    );

    return (
        <div id="login">
            {username ? (
                <React.Suspense fallback={"User"}><NavMenuUser username={username} /></React.Suspense>
            ) : (
                <>{state ? <React.Suspense fallback={<LoadingMascot />}><Login /></React.Suspense> : <LoginButton />}</>
            )}
        </div>
    )
}

export default function Header(props) {
    const { username } = props;

    return (
        <>
            <NavMenu />
            <User username={username} />
            <Search />
        </>
    );
}
