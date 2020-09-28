import React from 'react';

import Modal from './Modal.jsx';
import { QuestionBlock } from '../lib/icons.js';

const API_ENDPOINT = `${process.env.API_ENDPOINT}/users`;

export default function Login(props) {
    const { username } = props;

    const form = React.useRef();
    const handleSubmit = (event) => {
        event.preventDefault();
        console.log(form.current.username.value, form.current.password.value);
    };

    const [open, setOpen] = React.useState(false);
    const handleOpen = (event) => {
        event.preventDefault();
        setOpen(true);
    };
    const handleClose = () => {
        setOpen(false);
    };

    const userLink = username && (
        <span className="user">
            <a href={`~${username}`}>{username}</a>
        </span>
    );

    const loginButton = (
        <a href="/login.php" title="Login" onClick={handleOpen} className="user user-unknown">
            <QuestionBlock className="user-avatar thumbnail" />
            <span className="user-username">Login</span>
        </a>
    );

    return (
        <div id="login">
            {userLink || loginButton}
            <Modal open={open} close={handleClose} closeButton={false}>
                <form ref={form} onSubmit={handleSubmit}>
                    <input type="text" name="username" placeholder="Username" ref={(input) => input && input.focus()} />
                    <input type="password" name="password" placeholder="Password" />
                    <div>
                        <button type="submit">Login</button>
                        <button type="button" onClick={handleClose}>Cancel</button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
