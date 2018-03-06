# Ark Web Request Filter

Ark defined an abstract class `ArkRequestFilter` to pre-handle the request.
For users, there are mainly three methods to turn attentions.

An instance of `ArkRequestFilter` must implement the methods definition of `shouldAcceptRequest` and  `filterTitle`.
Also, you might use method `hasPrefixAmong` to do some path prefix based condition judgement.

To define and make use of a filter, you should first define a class extending `ArkRequestFilter`.
Within the filter class, you should define a method `filterTitle` and return a string as name of the filter.
Then, you should think about the work routine of method `shouldAcceptRequest`.
It receives parameters as

1. path : input
1. method : input
1. params : input
1. preparedData : input and output
1. responseCode : output 
1. error : output