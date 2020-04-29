1.websocket.php 为服务端，启动：php websocket.php
2.click_chat.php 为聊天框切换接口，主要用于创建关联临时会话（房间号）
3.ws_client.html 为游客客户端
4.ws_customer.html 为客服客户端

注：该demo采用面向过程的方式编写，好多地方仅仅是为了实现功能。真正开发需要注意下面几点：
1.uid的获取
2.临时会话标识的关联获取，即通过click_chat.php创建的临时会话，如何在游客/会员推送消息的时候进行关联（如何找到这个房间号）
3.使用面向对象
4.相关变量常驻内存