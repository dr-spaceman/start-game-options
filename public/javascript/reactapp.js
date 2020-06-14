'use strict';

const e = React.createElement;

const useEffect = React.useEffect;

function Lb() {
    const [counter, useCounter] = useEffect(0)

    return <button>Counter++</button>
}

class LikeButton extends React.Component {
    constructor(props) {
        super(props);
        this.state = { liked: false };
    }

    render() {
        if (this.state.liked) {
            return 'You liked this.';
        }

        return e(
            'button',
            { onClick: () => this.setState({ liked: true }) },
            'Like'
        );
    }
}

const domContainer = document.querySelector('#root');
ReactDOM.render(Lb(), domContainer);
