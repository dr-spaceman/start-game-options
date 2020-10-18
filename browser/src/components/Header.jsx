import React from 'react';
import TopNav from './TopNav.jsx';
import Search from './Search.jsx';
import { QuestionBlock, LoadingMascot } from '../lib/icons.js';

const TopNavUser = React.lazy(() => import(/* webpackChunkName: "TopNavUser" */'./TopNavUser.jsx'));
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
                <React.Suspense fallback={"User"}><TopNavUser username={username} /></React.Suspense>
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
            <TopNav />
            <User username={username} />
            <Search />
        </>
    );
}
